<?php

namespace App\Command;

use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\String\Slugger\SluggerInterface;
use Carbon\Carbon;
use Symfony\Component\Uid\Uuid;
use function Symfony\Component\String\u;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\{
	Path,
	Filesystem
};
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Helper\{
    ProgressBar,
    Table
};
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Validator\{
    Constraints,
    Validation
};
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\{
    TableSeparator
};
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Completion\{
    CompletionSuggestions,
    CompletionInput
};
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\{
    AsCommand
};
use Symfony\Component\Console\Input\{
    InputArgument,
    InputOption,
    InputInterface
};
use Symfony\Component\Console\Output\{
    OutputInterface
};
use App\Service\ArrayService;
use App\Service\StringService;
use App\Service\RegexService;

/*
*/
#[AsCommand(
    name: 'join',
)]
class JoinCommand extends AbstractCommand
{
	use LockableTrait;
	
	const DESCRIPTION = ''
		. 'Video in current directory join with audio with the same name'
		. '(.ext doesn\'t consider)'
	;
	
	const INPUT_AUDIO_FIND_DEPTH = ['== 0', '== 1'];/* first 0 then 1 */
	
	/*
		[
			0 => [
				'inputVideoFilename'		=> '<string>',
				'inputAudioFilename'		=> '<string>',
				'outputVideoFilename'		=> '<string>',
			]
			...
		]
	*/
	private array $commandParts		= [];
	private ?string $fromRoot		= null;
	private ?string $toDirname		= null;
	private int $allVideosCount		= 0;
	
	//###> DEFAULT ###

    public function __construct(
		$devLogger,
		$t,
		$progressBarSpin,
		//
		private readonly ArrayService $arrayService,
		private readonly StringService $stringService,
		private readonly RegexService $regexService,
		private readonly SluggerInterface $slugger,
		private readonly Filesystem $filesystem,
		private readonly string $ffmpegAbsPath,
		private readonly string $supportedFfmpegVideoFormats,
		private readonly string $supportedFfmpegAudioFormats,
		private readonly string $joinTitle,
		private readonly string $endTitle,
		private readonly string $figletAbsPath,
		private readonly string $ffmpegAlgorithmForInputVideo,
		private readonly string $ffmpegAlgorithmForInputAudio,
		private readonly string $ffmpegAlgorithmForOutputVideo,
		private readonly string $endmarkOutputVideoFilename,
		private $carbonFactory,
	) {
        parent::__construct(
			devLogger:			$devLogger,
			t:					$t,
			progressBarSpin:	$progressBarSpin,
		);
    }

    protected function configure(): void
    {
		parent::configure();
		
		$this
			//###> ARGUMENTS ###
		    //###> OPTIONS ###
            //###> HELP ###
			->setHelp($this->t->trans(self::DESCRIPTION))
			->setDescription($this->t->trans(self::DESCRIPTION))
		;
    }

    protected function initialize(
        InputInterface $input,
        OutputInterface $output,
    ) {
		parent::initialize($input, $output);
		
		$this->fromRoot = $this->getRoot();
    }
	
    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
		//###> SCENARIO ###
		$this->dimpHelpInfo($output);
		
		$this->fillInCommandParts();
		
		$this->dumpCommandParts($output);
		
		$this->isOk();
		
		$this->lockOrExit();
		
		$this->ffmpegExec($output);
		
		$this->io->success($this->endTitle);
		//###< SCENARIO ###
		
		return Command::SUCCESS;
    }
	
	
	//###> HELPER ###
	
	private function lockOrExit(): void {
		if (!$this->lock($this->getHashOfProcess())) {
			$this->exit(
				$this->t->trans(
					'Команда %command% уже запущена для этой директории',
					parameters: [
						'%command%' => $this->getName(),
					],
				)
			);
		}
	}
	
	private function assignNonExistentToDirname(): void {
		/* more safe
		$newDirname = (string) $this->slugger->slug(
			(string) $this->carbonFactory->make(new \DateTime)
		);
		*/
		$newDirname = \str_replace(':', '_', (string) $this->carbonFactory->make(new \DateTime));
		
		while (\is_dir($newDirname)) $newDirname = (string) $this->slugger->slug(Uuid::v1());
		$this->toDirname	= $newDirname;
	}
	
	private function dimpHelpInfo(
		OutputInterface $output,
	): void {
		
		$this->io->section(
			$this->t->trans(
				'###> СПРАВКА ###',
				parameters: [],
			)
		);
		$output->writeln('<bg=black;fg=yellow> [NOTE] '
			. $this->t->trans('Открывай консоль в месте расположения видео.')
			. '</>'
		);
		$this->io->info([
			$this->t->trans('Для того чтобы к видео был найден нужный аудио файл:'),
			$this->t->trans('1) Аудио файл должен быть назван в точности как видео файл (расширение не учитывается)'),
			$this->t->trans('2) Аудио файл должен находится во вложенности не более 1 папки относительно видео'),
		]);
		$output->writeln('<bg=black;fg=yellow> [NOTE] '
			. $this->t->trans(''
				. 'Для объединённых видео файлов создаётся новая, гарантированно уникальная папка.'
			)
			. '</>'
		);
		$this->io->info([
			$this->t->trans('Программа объёдиняет видео с аудио в новый видео файл'),
			$this->t->trans('Исходные видео и аудио остаются прежними как есть (не изменяются)'),
		]);
		$this->io->section($this->t->trans('###< СПРАВКА ###'));
		
	}
	
	private function ffmpegExec(
		OutputInterface $output,
	): void {
		if (empty($this->commandParts) || $this->toDirname === null) {
			$this->io->error($this->t->trans('ERROR'));
			return;
		}
		
		$this->filesystem->mkdir($this->stringService->getPath($this->getRoot(), $this->toDirname));
		
		$resultsFilenames = [];
		\array_walk($this->commandParts, function(&$commandPart) use (&$resultsFilenames, &$output) {
			[
				'inputVideoFilename'		=> $inputVideoFilename,
				'inputAudioFilename'		=> $inputAudioFilename,
				'outputVideoFilename'		=> $outputVideoFilename,
			] = $commandPart;
			
			// ffmpeg algorithm
			$command	= '"' . $this->stringService->getPath($this->ffmpegAbsPath) . '"'
				. (string) u($this->ffmpegAlgorithmForInputVideo)->ensureEnd(' ')->ensureStart(' ') 
				. '"' . $inputVideoFilename . '"'
				. (string) u($this->ffmpegAlgorithmForInputAudio)->ensureEnd(' ')->ensureStart(' ')
				. '"' . $inputAudioFilename . '"'
				. (string) u($this->ffmpegAlgorithmForOutputVideo)->ensureEnd(' ')->ensureStart(' ')
				. '"' . $outputVideoFilename . '"'
			;
			//\dd($command);
			
			$result_code = 0;
			\system($command, $result_code);
			
			$this->devLogger->info(
				'$result_code: ' . $result_code, ['$outputVideoFilename' => $outputVideoFilename],
			);
			
			if ($result_code != 0) $this->shutdown();
			
			// dump
			$outputVideoFilename = $this->makePathRelative($outputVideoFilename);
			$this->io->warning([
				$outputVideoFilename . (string) u('('.$this->t->trans('ready').')')->ensureStart(' '),
			]);

			$resultsFilenames []= $outputVideoFilename;
		});
		
		$this->io->info([
			$this->t->trans('ИТОГ:'),
			...$resultsFilenames,
		]);
	}
	
	private function fillInCommandParts(): void {
		$this->assignNonExistentToDirname();
		
		$humanSort = static fn($l, $r): bool => (((int) \preg_replace('~[^0-9]+~', '', $l)) > ((int) \preg_replace('~[^0-9]+~', '', $r)));

		$finderInputVideoFilenames = (new Finder)
			->in($this->fromRoot)
			->files()
			->sort($humanSort)
			->depth('== 0')
			->name($regex = '~^.+' . $this->supportedFfmpegVideoFormats . '$~i')
		;
		
		foreach($finderInputVideoFilenames as $finderInputVideoFilename) {
			++$this->allVideosCount;
			
			$inputAudioFilename		= $this->getInputAudioFilename($finderInputVideoFilename);
			if ($inputAudioFilename === null) continue;
			
			$inputVideoFilename		= $finderInputVideoFilename->getFilename();

			$outputVideoFilename	= ''
				
				. $finderInputVideoFilename->getFilenameWithoutExtension()
				. $this->endmarkOutputVideoFilename
				// OR INSTEAD, JUST ENSURE
				//. (string) u($finderInputVideoFilename->getFilenameWithoutExtension())->ensureEnd($this->endmarkOutputVideoFilename)
				
				. '.'
				. $finderInputVideoFilename->getExtension()
			;
			
			$this->commandParts		[]=
			[
				'inputVideoFilename'		=> $this->stringService->getPath($this->fromRoot, $inputVideoFilename),
				'inputAudioFilename'		=> $this->stringService->getPath($this->fromRoot, $inputAudioFilename),
				'outputVideoFilename'		=> $this->stringService->getPath($this->fromRoot, $this->toDirname, $outputVideoFilename),
			];
		}
	}
	
	private function dumpCommandParts(
		OutputInterface $output,
	): void {
		if (empty($this->commandParts)) {
			$this->io->success(
				$this->t->trans('Нечего соединять')
			);
			exit();
		}
		
		$this->beautyDump($output);
		
		$infos = [
			$this->t->trans('Видео'),
			$this->t->trans('Аудио'),
			$this->t->trans('Результат'),
		];
		
		foreach($this->commandParts as [
			'inputVideoFilename'		=> $inputVideoFilename,
			'inputAudioFilename'		=> $inputAudioFilename,
			'outputVideoFilename'		=> $outputVideoFilename,
		]) {
			$inputVideoFilename				= $this->makePathRelative($inputVideoFilename);
			$inputAudioFilename				= $this->makePathRelative($inputAudioFilename);
			$outputVideoFilename			= $this->makePathRelative($outputVideoFilename);
			
			$output->writeln(
				\str_pad($infos[0], $this->stringService->getOptimalWidthForStrPad($infos[0], $infos))
				. '"<bg=yellow;fg=black>' . $inputVideoFilename . '</>"'
			);
			$output->writeln(
				\str_pad($infos[1], $this->stringService->getOptimalWidthForStrPad($infos[1], $infos))
				. '"<bg=white;fg=black>' . $inputAudioFilename . '</>"'
			);
			$output->writeln(
				\str_pad($infos[2], $this->stringService->getOptimalWidthForStrPad($infos[2], $infos))
				. '"<bg=green;fg=black>' . $outputVideoFilename . '</>"'
			);
			$output->writeln('');
		}
		
		$sumUpStrings = [
			$this->t->trans('Всего видео:'),
			$this->t->trans('Видео с переводами:'),
		];
		$output->writeln(
			'<bg=black;fg=yellow>'
			. \str_pad(
				$sumUpStrings[0],
				$this->stringService->getOptimalWidthForStrPad($sumUpStrings[0], $sumUpStrings)
			) . $this->allVideosCount . '</>'
		);
		$output->writeln(
			'<bg=black;fg=yellow>'
			. \str_pad(
				$sumUpStrings[1],
				$this->stringService->getOptimalWidthForStrPad($sumUpStrings[1], $sumUpStrings)
			) . $videosWithAudio = \count($this->commandParts) . '</>'
		);
		$output->writeln('');
		
		$this->io->warning(
			$this->t->trans('Для немедленного прекращения операции нажми сочетание клавиш: "Ctrl + C"')
		);
	}
	
	private function beautyDump(
		OutputInterface $output,
	): void {
		//$output->writeln('');
		$figletRoot = \dirname($this->figletAbsPath);
		$command = ''
			. ' cd "' . $figletRoot . '" &&'
			
			. ' ' . '"' . $this->figletAbsPath . '"'
			
			// font: without .ext
			//. ' ' . '-f "' . $this->stringService->getPath($figletRoot, 'fonts/Moscow') . '"'
			. ' ' . '-f "' . $this->stringService->getPath($figletRoot, 'fonts/3d_diagonal') . '"'
			
			. ' ' . '-c'
			. ' ' . ' -- "' . $this->joinTitle . '"'
		;
		\system($command);
		$output->writeln('');
	}	
	
	private function getInputAudioFilename(
		SplFileInfo $finderInputVideoFilename,
	): ?string {
		$inputAudioFilename					= null;
		$inputVideoFilenameWithoutExtension = $finderInputVideoFilename->getFilenameWithoutExtension();
		
		$finderInputAudioFilenames			= (new Finder)
			->in($this->fromRoot)
			->files()
			->depth(self::INPUT_AUDIO_FIND_DEPTH)
			->name(
				$regex = '~^'
					. $this->regexService->getEscapedStrings($inputVideoFilenameWithoutExtension)
					. $this->supportedFfmpegAudioFormats
					. '$~'
			)
		;
		
		$inputAudioFilenames = \array_values(
			\array_map(
				static fn($v) => $v->getRelativePathname(),
				\iterator_to_array($finderInputAudioFilenames),
			)
		);
		
		if (isset($inputAudioFilenames[0])) $inputAudioFilename = $inputAudioFilenames[0];
		
		return $inputAudioFilename;
	}

	private function getRoot(): string {
		return Path::normalize(\getcwd());
	}
	
	private function makePathRelative(string $needyPath): string {
		return \rtrim($this->filesystem->makePathRelative($needyPath, $this->fromRoot), '/');
	}
	
	private function getHashOfProcess(): string {
		return \md5($this->getRoot());
	}
}
