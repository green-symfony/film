![join film](https://github.com/green-symfony/film/blob/main/doc/film%20join%20working.gif)

What this program about?
---
`film join` is based on ffmpeg.exe, and uses for join video and audio in one video file.

Undoubtedly, you can change ffmpeg's algorithm (see Advanced section).

It's highly useful for anime-watchers. Download and join video with audio translations.

Instalaion
---
0. You should install:
	[php](https://www.php.net/downloads.php)
	[composer](https://getcomposer.org/download/)
Optional:
[git_bash](https://git-scm.com/downloads)
1. Open terminal in this directory (in path line write "cmd" and push "Enter")
2. Execute `composer install`
3. Execute `php ./bin/film c:c`
4. Add absolute path to "bin" directory into "Windows Environment Variables"
5. It's over! Open a new console.

Now, in git_bash console, you can use the command `film join` for join video and audio

Or, if you didn't set up git_bash console, you can use usual windows console (cmd)
and execute the following command `php film join`

Advanced
---
You can change particular behaviour.
1. Create in root directory of this project a new file `.env.local`
2. Copy from `.env` file section `###> APP (CHANGE ME) ###`
3. Change `###> APP (CHANGE ME) ###` section
4. For instance (in `.env.local` file):
```.env
###> APP (CHANGE ME) ###
LOCALE='en_US'
TIMEZONE='+8:00'

# Decoration
JOIN_TITLE=Good
END_TITLE="Files were joined!"

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
