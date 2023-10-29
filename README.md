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
	[composer](https://getcomposer.org/download/),
	[git_bash](https://git-scm.com/downloads)
1. Open git_bash into ROOT_DIRECTORY_OF_THIS_PROJECT and execute `./init.sh` file
2. Add absolute path to "ROOT_DIRECTORY_OF_THIS_PROJECT/bin" directory into "Operation System Environment Variables"
3. [Finally](#final-step)

### Final step

Restart the git_bash console.
You can already use the command `film join` for join video with audio.

Or, if you haven't set up git_bash console, you can use the usual windows console (cmd)
and execute the following command `php "ROOT_DIRECTORY_OF_THIS_PROJECT/bin/film" join`

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
SUPPORTED_FFMPEG_VIDEO_FORMATS="MP4|AVI|MOV|FLV|WMV"
SUPPORTED_FFMPEG_AUDIO_FORMATS="mp3|flac|aac|wav|mka|ogg"

# ffmpeg algorithm of converting
FFMPEG_ALGORITHM_FOR_INPUT_VIDEO="-i"
# move (the first index of -map 1:a:0 is audio) 1.1sec right
FFMPEG_ALGORITHM_FOR_INPUT_AUDIO="-itsoffset 1.1 -i"
FFMPEG_ALGORITHM_FOR_OUTPUT_VIDEO="-c:v copy -c:a aac -map 0:v:0 -map 1:a:0"

# endmark (without endmark, with the same name as a source)
ENDMARK_OUTPUT_VIDEO_FILENAME=""
###< APP (CHANGE ME) ###
```
