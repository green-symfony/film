![join film](https://github.com/green-symfony/film/blob/main/doc/film%20join%20working.gif)

What this program about?
---
`film join` is based on ffmpeg.exe, and uses for join video and audio in one video file.

Undoubtedly, you can change ffmpeg's algorithm (see Advanced section).

It's highly useful for anime-watchers. Download and join video with audio translations.

Installation
---
0. You should install:
	[php](https://www.php.net/downloads.php),
	[composer](https://getcomposer.org/download/)
and optional:
[git_bash](https://git-scm.com/downloads)
1. [Install the bundles](#installation-the-bundles-step)
2. Add absolute path to "ROOT_DIRECTORY_OF_THIS_PROJECT/bin" directory into "Operation System Environment Variables"
3. [Finally](#final-step)

### Installation the bundles step

[Before git clone](https://github.com/green-symfony/docs/blob/main/docs/bundles_green_symfony%20mkdir.md)

```console
git clone "https://github.com/green-symfony/command-bundle.git"
```

```console
git clone "https://github.com/green-symfony/service-bundle.git"
```

Open your console into your main project directory and execute:

```console
composer install
```

```console
php ./bin/film c:c
```
### Final step

Restart the git_bash console.
You can already use the command `film join` for join video with audio.

Or, if you didn't set up git_bash console, you can use usual windows console (cmd)
and execute the following command `php "PATH_TO_FILM_FILE" join`

Advanced
---
You can change the defined behaviour.
1. Create in root directory of this project a new file `touch ./.env.local`
2. Copy from `.env` file section `###> APP (CHANGE ME) ###`
3. Change `###> APP (CHANGE ME) ###` section
4. For instance (in `.env.local` file):
```.env
###> APP (CHANGE ME) ###
LOCALE='en_US'
TIMEZONE='+8:00'

# Decoration
JOIN_TITLE=Good
END_TITLE="The files were joined!"

# ffmpeg video and audio formats for searching
SUPPORTED_FFMPEG_VIDEO_FORMATS="[.](?:MP4|AVI|MOV|FLV|WMV)"
SUPPORTED_FFMPEG_AUDIO_FORMATS="[.](?i)(?:mp3|flac|aac|wav|mka|ogg)"

# ffmpeg algorithm of converting
FFMPEG_ALGORITHM_FOR_INPUT_VIDEO="-i"
FFMPEG_ALGORITHM_FOR_INPUT_AUDIO="-i"
FFMPEG_ALGORITHM_FOR_OUTPUT_VIDEO="-c:v copy -c:a aac -map 0:v:0 -map 1:a:0"

# endmark (without endmark, with the same name as a source)
ENDMARK_OUTPUT_VIDEO_FILENAME=""
###< APP (CHANGE ME) ###
```
