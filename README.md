![join film](https://github.com/green-symfony/film/blob/main/doc/film%20join%20working.gif)

What's this program about?
---

`film join` is based on the ffmpeg program, and uses to join video and audio in one video file.

Undoubtedly, you can change ffmpeg's algorithm (see [Advanced](#advanced) section).

It's highly useful for anime-watchers.
As a rule of thumb, translations are never joined with animes FULL HD or higher.
Download and join video with chosen audio translations.

Installation
---

1. [Install all of it](#install-all-of-it)
1. [Download the program](#download-the-program)
1. [Init](#init)
1. [Operation System Environment Variables](#operation-system-environment-variables)
1. [Final step](#final-step)
1. [Addition](#addition)
1. [How to update?](#update)
1. [Advanced](#advanced)

### Install all of it

[Install all of it](https://github.com/green-symfony/docs/blob/main/docs/all%20the%20necessary%20programms%20for%20project.md)

### Download the program

Choose the directory where you want to situate this program.
After you've chosen just open OS console and execute
```console
git clone "https://github.com/green-symfony/film.git"
```
and ROOT_DIRECTORY_OF_THIS_PROJECT will appear

### Init

Open git_bash into ROOT_DIRECTORY_OF_THIS_PROJECT and execute 
```console
./init.sh
```

### Operation System Environment Variables

Add the absolute path to "ROOT_DIRECTORY_OF_THIS_PROJECT/bin" directory into "Operation System Environment Variables"

Google it if you don't know what it's about.

### Final step

Restart the git_bash terminal.
You can already use the command 
```console
film join
```
in the directory where videos place and join video with audio on the particular depth.

Or, if you haven't set up git_bash console, you can use the usual windows console (cmd)
and execute the following command `php "ROOT_DIRECTORY_OF_THIS_PROJECT/bin/film" join`

But you have to admit that's not convenient.

### Addition

If your OS System is not Windows you must:

1. Download [ffmpeg](https://ffmpeg.org/download.html).
1. Place it in the project or somewhere else, for example in the `ROOT_DIRECTORY_OF_THIS_PROJECT/bin/exe/ffmpeg/EXECUTION_FILE`
1. According to the chosen path write it down in your `ROOT_DIRECTORY_OF_THIS_PROJECT/.env.local`

```.env
FFMPEG_ABSOLUTE_PATH="%kernel.project_dir%/bin/exe/ffmpeg/EXECUTION_FILE"
```

### Update

To update this project just open git_bash into ROOT_DIRECTORY_OF_THIS_PROJECT and execute:
```console
./update.sh
```

### Advanced
---

You can change the defined behaviour.
1. Create in root directory of this project a new file `touch ./.env.local`
1. Copy from `.env` file section `###> APP (CHANGE ME) ###`
1. Change `###> APP (CHANGE ME) ###` section, for instance, we can write down the following:
```.env
# That's the ".env.local" file

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
