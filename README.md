![join film](https://cloud.mail.ru/public/ybR6/eBNynN2RZ#gif)

What this program about?
---
`film join` is based on ffmpeg.exe, and uses for join video and audio in one vedeo file.

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
Now, in git_bash console, you can use the command `film join` for join video and audio.
5.1. Or, if you didn't set up git_bash console, you can use usual windows console (cmd)
and execute the following command `php film join`

Advanced
---
You can change particular behaviour.
1. Create in root directory of this project a new file `.env.local` and copy from `.env` file section `###> APP (CHANGE ME) ###`
2. Change `###> APP (CHANGE ME) ###` section
For instance (in `.env.local` file):
`.env
###> APP (CHANGE ME) ###
JOIN_TITLE=Good
END_TITLE="Файлы соединены"
###< APP (CHANGE ME) ###
`
