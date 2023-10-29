![join film](https://github.com/green-symfony/film/blob/main/doc/film%20join%20working.gif)

What this program about?
---


`film join` is based on ffmpeg.exe, and uses for join video and audio in one video file.

Undoubtedly, you can change ffmpeg's algorithm (see Advanced section).

It's highly useful for anime-watchers. Download and join video with audio translations.

Installation
---

0. [Step 1](#step-1)
1. [Step 2](#step-2)
2. [Step 3](#step-3)
3. [Step 4](#step-4)
4. [Finally](#final-step)
5. [Addition](#addition)

### Step 1

You should install:
[php](https://github.com/green-symfony/docs/blob/main/docs/php%20installation.md),
[composer](https://getcomposer.org/download/),
[git_bash](https://git-scm.com/downloads)

### Step 2

Choose the directory where you want to situate this programm.
After you've chosen just open OS console and execute
```console
git clone "https://github.com/green-symfony/film.git"
```
and ROOT_DIRECTORY_OF_THIS_PROJECT will appear

### Step 3

Open git_bash into ROOT_DIRECTORY_OF_THIS_PROJECT and execute 
```console
./init.sh
```

### Step 4

Add the absolute path to "ROOT_DIRECTORY_OF_THIS_PROJECT/bin" directory into "Operation System Environment Variables"

### Final step

Restart the git_bash console.
You can already use the command 
```console
film join
```
in the directory where videos place and join video with audio.

Or, if you haven't set up git_bash console, you can use the usual windows console (cmd)
and execute the following command `php "ROOT_DIRECTORY_OF_THIS_PROJECT/bin/film" join`

### Addition

If your OS System is not Windows you must downlod the builded [ffmpeg](https://ffmpeg.org/download.html) on your own.

0) Download [ffmpeg](https://ffmpeg.org/download.html).

1) Place it in the project, for example in the `ROOT_DIRECTORY_OF_THIS_PROJECT/bin/exe/ffmpeg/DIRECTORY_WHERE_EXECUTION_FILE_PLACES`

2) According to the path write it down in your `ROOT_DIRECTORY_OF_THIS_PROJECT/.env.local`

```.env
FFMPEG_ABSOLUTE_PATH="%kernel.project_dir%/bin/exe/ffmpeg/DIRECTORY_WHERE_EXECUTION_FILE_PLACES"
```

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
