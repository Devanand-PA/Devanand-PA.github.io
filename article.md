# Table of Contents

[Oct 15, 2026]{.small}

::::::::::::::::::::::::::::::::::::::::: {}
`<style> code { background-color : #000000; } </style>`{=html}

# Table of Contents {#ToC_link}

1.  [Table of Contents](#ToC_link)
2.  [About the Terminal](#heading-1)
3.  [About the Shell](#heading-2)
4.  [The Shell as a File Manager](#heading-3)
5.  [Running Commands in the Shell](#heading-4)
6.  [The PATH to commands](#heading-5)
7.  [simple logic in bash](#heading-6)
8.  [Bang! Bang! She-Bang!](#heading-7)

## About the Terminal {#heading-1}

If you\'re reading this, chances are that you have come across a terminal already. A terminal is , at the most basic level, some medium that displays some kind of information. When we talk about the first computers, which were made well over half a century ago, we talk about terminals that used paper to produce output and used punched cards to take input.

Nowadays, most terminals render output to a screen. The ones we are talking about here in particular, are the ones that are present in most unix like operating systems. They output to a screen, but usually , when one talks of say, a linux terminal, one doesn\'t talk of high end graphics. The point I am trying to make is that most terminals output plain text, and usually nothing more than plain text. (There are some exceptions , such as sixel, as well as kitty\'s protocols, etc)

Most unix like operating systems have a built in terminal even if they don\'t have graphical output. This terminal judges what kind of output to give depending on what devices are available. The \"terminal\" here is simply a program that can output text to a screen and take in input. By itself, it doesn\'t do much, but the best part about it is that we can write programs to tell it what to output and when to take in input. A classic example of such a program that takes advantage of the terminal is the classic \"hello world\" program, which is usually the first program that people write regardless of which programming language they are writing in. However, we can make it do a lot of other things as well.

Another example is say, a login screen, where the terminal asks the user for input (their username and password), passing those values back to the \"login screen\" program to check the credentials. Now there is a lot you can do just by outputting and manipulating text. For example, you can control the operating system itself. And this is actually a common use case. In fact the programs that allow you to control your operating system using a terminal are called \"system shells\".

Now , using the default \"linux terminal\" (or more accurately, the \"linux console\") might be a headache , because by default it provides only one (actually 12, from F1..F12) screens for you to do anything with. The workings of this \"console\" are more \"internal\" than regular programs, but for now we need something that can do the same things that the \"console\" can do, but without the restrictions of having to stay without a GUI (ever !) and having only one screen to stare at.

![The linux console](/home/devanandpa/.hermes/cache/scratch/tmp.IDrfYBVnNd/17d0bfd8660e3969d22e4b569ed8c1c44ade39b5.png)

That is where a \"Terminal Emulator\" comes in. Simply put, it is a regular program that mimics the linux console. And just like the linux console, it is also, a \"terminal\" , in that it\'s job is also simply to display text and take in text input. And again, that is all you need.

Now, while the linux console starts with a \"login screen\" (called the \"login shell\", because it too, \"controls\" the operating system in a way), most terminal emulators default to more \"standard\" system shells.

What do I mean by \"standard\" ? Well for one, there\'s the POSIX standard, but some of these shells do not abide by it, but what I am talking about is the fact that these shells follow a similar structure. That is , they are a programming language with easy access to the operating system\'s internal functions.

The \"shells\" are programs that use the terminal (in this case, the terminal emulator) as a means to communicate with the user. Among the system shells in unix like OSes, bash is probably the most well known. Now I must clarify again, when you open a terminal emulator on your desktop, all the text you are seeing are being \"rendered\" by the terminal, but the \"shell\" is the one telling the terminal to render that so that it can , well, fulfill it\'s purpose.

## About the Shell {#heading-2}

Since there are so many shells in unix like OSes, we\'ll keep things simple and restrict out discussions to bash. Of course, you are free to use the python shell or the c shell or even the Holy C shell if you like, but they have slight syntactic differences, though they all function in the same way.

Now before we move forward, we need to make sure that everybody is using \"bash\". for that, simply open a terminal and type \"bash\" into it. It should probably open bash, because it should be installed by default. If that doesn\'t work, just type \"sh\" and we\'ll do with that for now.

Now whichever shell you found yourself in, it follows some general rules. Try typing random things in it and press enter and see what happens. You might find out that some things seem to do something while others return an error.

Why is that ?

Well, like I said ,most shells follow some basic rules.

- each \"word\" separated by one or more whitespaces is considered an \"argument\" (it is possible to write literal whitespaces using `" "` or `' '` or just `\ `)

- the first argument is searched up until it turns out to be one among the following

  - a builtin function of the shell
  - a user defined function
  - a user defined \"alias\"
  - a path to a file
  - a filepath once completed with one of the directories listed in a variable called \"PATH\"

  (in general, if the first argument is not a shell function/builtin/alias, then it is treated as a filepath, and the shell checks if it is executable. If it is , it tries to run the program specified in the filepath. In case you provide something that does not fulfil any of these conditions, the shell will just give you an error)

## The Shell as a File Manager {#heading-3}

## The Shell as a File Manager {#heading-3}

One of the most powerful aspects of the shell is its ability to function as a file manager. In fact, before graphical file managers existed, people managed their files entirely through the command line. Even today, many system administrators and developers prefer the shell for file management tasks because it's faster, more precise, and scriptable.

When you open a shell, you're typically placed in your home directory. You can verify this by using the `pwd` command, which stands for "print working directory":

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
pwd
```
:::

This will output something like `/home/yourusername`, showing you exactly where you are in the filesystem.

To see what files and directories are in your current location, you use the `ls` command (short for "list"):

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
ls
```
:::

This will show you the contents of your current directory. For more detailed information, you can use the `-l` flag (for "long format"):

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
ls -l
```
:::

This will show you permissions, ownership, size, and modification time for each file and directory.

You can also combine flags. For example, to see all files (including hidden ones that start with a dot) in long format:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
ls -la
```
:::

Navigating between directories is done with the `cd` command (short for "change directory"). To go to a specific directory:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
cd /path/to/directory
```
:::

To go up one directory level:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
cd ..
```
:::

To go back to your home directory from anywhere:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
cd
```
:::

Or equivalently:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
cd ~
```
:::

The tilde (`~`) is a shorthand for your home directory.

Creating directories is done with the `mkdir` command (short for "make directory"):

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
mkdir new_directory
```
:::

You can create multiple directories at once:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
mkdir dir1 dir2 dir3
```
:::

Or create a directory tree with the `-p` flag (which creates parent directories as needed):

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
mkdir -p parent/child/grandchild
```
:::

Removing files and directories requires caution, as there's no trash bin in the shell by default. To remove a file:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
rm filename
```
:::

To remove an empty directory:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
rmdir directory_name
```
:::

To remove a directory and all its contents (use with extreme caution!):

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
rm -rf directory_name
```
:::

The `-r` flag means "recursive" (go into subdirectories) and `-f` means "force" (don't ask for confirmation).

Copying files is done with the `cp` command:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
cp source_file destination_file
```
:::

To copy a directory and all its contents:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
cp -r source_directory destination_directory
```
:::

Moving or renaming files is done with the `mv` command:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
mv old_name new_name
```
:::

Or to move a file to a different directory:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
mv file.txt /path/to/destination/
```
:::

You can also move multiple files at once:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
mv file1.txt file2.txt /path/to/destination/
```
:::

## Running Commands in the Shell {#heading-4}

Now that we understand how to navigate and manage files in the shell, let's look at how to actually run commands and programs.

As we discussed earlier, when you type a command in the shell, it goes through a specific process to determine what to execute. Let's review that process with some practical examples.

When you type a command like `ls`, the shell:

1.  Checks if `ls` is a builtin command (it's not in bash - `ls` is an external program)
2.  Checks if you've defined a function named `ls` (you probably haven't)
3.  Checks if you've created an alias for `ls` (many systems have `ls` aliased to `ls --color=auto` by default)
4.  Looks for `ls` as a file in the directories listed in your `PATH` variable

Most of the commands you'll use daily are external programs stored in directories like `/bin`, `/usr/bin`, `/usr/local/bin`, etc. These are found through the `PATH` mechanism we'll discuss in the next section.

Let's try running some simple commands to see how they work:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
echo "Hello, World!"
```
:::

This will output the text "Hello, World!" to your terminal. The `echo` command simply prints its arguments.

You can also combine commands using pipes and redirection, but we'll get to that in a later section.

For now, let's look at how to run a command and capture its output. You can store the output of a command in a variable like this:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
current_dir=$(pwd)
echo "You are currently in: $current_dir"
```
:::

This runs the `pwd` command, captures its output, stores it in the variable `current_dir`, and then prints it.

Another useful technique is running a command in the background. If you add an ampersand (`&`) to the end of a command, it will run in the background:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
sleep 30 &
```
:::

This starts a `sleep` command that will wait for 30 seconds, but because it's running in the background, you'll immediately get your prompt back and can continue working.

You can see what background jobs you have running with the `jobs` command:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
jobs
```
:::

And you can bring a background job to the foreground with the `fg` command.

## The PATH to Commands {#heading-5}

We mentioned the `PATH` variable briefly when discussing how the shell finds commands to execute. Let's take a closer look at this important concept.

The `PATH` variable is an environment variable that contains a list of directories, separated by colons (`:`). When you type a command, the shell searches through each directory in your `PATH` (in order) looking for an executable file with that name.

You can view your current `PATH` with:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
echo $PATH
```
:::

This will output something like:

``` {tabindex="0"}
/usr/local/bin:/usr/bin:/bin:/usr/local/games:/usr/games
```

Each of these directories is searched in order when you type a command. So if you have a file named `myscript` in both `/usr/local/bin` and `/usr/bin`, the version in `/usr/local/bin` will be used because it comes first in the `PATH`.

You can add directories to your `PATH` temporarily (for the current session) like this:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
export PATH="$PATH:/new/directory"
```
:::

Or to add a directory to the beginning of your `PATH` (so it's searched first):

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
export PATH="/new/directory:$PATH"
```
:::

If you want to make this change permanent, you'll need to add the `export` command to your shell's startup file (like `~/.bashrc` or `~/.bash_profile`).

Let's look at a practical example. Suppose you've written a useful script and saved it in `~/scripts/mytool`. You could run it every time by typing the full path:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
/home/yourusername/scripts/mytool
```
:::

But that's tedious. Instead, you can add `~/scripts` to your `PATH`:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
export PATH="$PATH:$HOME/scripts"
```
:::

Now you can simply type:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
mytool
```
:::

And the shell will find it in your `~/scripts` directory.

It's also worth noting that for security reasons, the current directory (`.`) is not included in the `PATH` by default. This means you can't run a script in the current directory just by typing its name. You need to explicitly specify the current directory:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
./myscript
```
:::

The `./` means "the current directory". This is a safety feature that prevents you from accidentally running a malicious script that might be disguised as a common command like `ls` or `cd`.

## Simple Logic in Bash {#heading-6}

So far we've looked at running individual commands, but the real power of the shell comes from being able to make decisions and repeat actions based on conditions. This is where bash's programming capabilities shine.

Bash supports several types of conditional statements, with the most common being the `if` statement. Here's the basic syntax:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
if [ condition ]; then
    # commands to run if condition is true
fi
```
:::

Let's look at a simple example that checks if a file exists:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
if [ -f "myfile.txt" ]; then
    echo "myfile.txt exists"
else
    echo "myfile.txt does not exist"
fi
```
:::

The `[ -f "myfile.txt" ]` part is a test that returns true if `myfile.txt` exists and is a regular file. There are many different tests you can perform:

- `-f file`: True if file exists and is a regular file
- `-d dir`: True if dir exists and is a directory
- `-x file`: True if file exists and is executable
- `-r file`: True if file exists and is readable
- `-w file`: True if file exists and is writable
- `-z string`: True if string is empty
- `-n string`: True if string is not empty
- `string1 = string2`: True if string1 equals string2
- `string1 != string2`: True if string1 does not equal string2
- `num1 -eq num2`: True if num1 equals numerically num2
- `num1 -ne num2`: True if num1 is not numerically equal to num2
- `num1 -lt num2`: True if num1 is less than num2
- `num1 -le num2`: True if num1 is less than or equal to num2
- `num1 -gt num2`: True if num1 is greater than num2
- `num1 -ge num2`: True if num1 is greater than or equal to num2

You can also combine conditions using logical operators:

- `&&` means AND
- `||` means OR
- `!` means NOT

For example:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
if [ -f "myfile.txt" ] && [ -r "myfile.txt" ]; then
    echo "myfile.txt exists and is readable"
fi
```
:::

This will only execute the echo command if both conditions are true: the file exists AND it's readable.

Another useful construct is the `case` statement, which is like a switch statement in other languages:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
case "$variable" in
    pattern1)
        # commands to run if variable matches pattern1
        ;;
    pattern2)
        # commands to run if variable matches pattern2
        ;;
    *)
        # commands to run if variable doesn't match any pattern
        ;;
esac
```
:::

Here's a practical example that might determine what action to take based on a file's extension:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
case "$filename" in
    *.txt)
        echo "This is a text file"
        cat "$filename"
        ;;
    *.jpg|*.jpeg)
        echo "This is a JPEG image"
        ;;
    *.mp3)
        echo "This is an MP3 audio file"
        ;;
    *)
        echo "Unknown file type: $filename"
        ;;
esac
```
:::

## Bang! Bang! She-Bang! {#heading-7}

We've been talking about running commands in the shell, but how do we actually create reusable scripts that we can run over and over? This is where the "shebang" (or "bang-bang" as some call it) comes in.

A shell script is simply a text file that contains a series of shell commands. To make it executable, you need to do two things:

1.  Add a shebang line at the very beginning of the file
2.  Make the file executable with the `chmod` command

The shebang line looks like this:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
#!/bin/bash
```
:::

This line must be the very first line in the file (no spaces or characters before it). It tells the operating system which interpreter to use to run the script. In this case, we're specifying that the script should be run using `/bin/bash`.

You could also use other shells:

- `#!/bin/sh` for the Bourne shell or a compatible shell
- `#!/usr/bin/env python3` for Python 3 (though this wouldn't be a bash script)
- `#!/usr/bin/env perl` for Perl

Using `/usr/bin/env` is often preferred because it's more portable across different systems:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
#!/usr/bin/env bash
```
:::

This tells the system to find the `bash` executable using the user's `PATH`, which is more flexible than hardcoding a path like `/bin/bash`.

Let's create a simple example script. We'll make a script that backs up a file by copying it with a timestamp:

::: highlight
``` {.bash data-lang="bash" tabindex="0" style="color:#f8f8f2;background-color:#272822;-moz-tab-size:4;-o-tab-size:4;tab-size:4;-webkit-text-size-adjust:none"}
#!/usr/bin/env bash

# Simple backup script

if [ $# -ne 1 ]; then
    echo "Usage: $0 filename"
    exit 1
fi

if [ ! -f "$1" ]; then
    echo "Error: File '$1' not found"
    exit 1
fi

TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP="$1.$TIMESTAMP.bak"

cp "$1" "$BACKUP"

echo "Backup created: $BACKUP"
```
:::

Let's break down what this script does:

1.  `#!/usr/bin/env bash` - The shebang line that tells the system to use bash to run this script
2.  `# Simple backup script` - A comment (lines starting with \# are comments in bash)
3.  `if [ $# -ne 1 ]; then` - Checks if the number of arguments (\$#) is not equal to 1
4.  `echo "Usage: $0 filename"` - Prints a usage message (\$0 is the name of the script)
5.  `exit 1` - Exits the script with an error code (1 indicates an error)
6.  `if [ ! -f "$1" ]; then` - Checks if the first argument (\$1) is not a file
7.  `TIMESTAMP=$(date +%Y%m%d_%H%M%S)` - Creates a timestamp string in the format YYYYMMDD_HHMMSS
8.  `BACKUP="$1.$TIMESTAMP.bak"` - Constructs the backup filename
9.  `cp "$1" "$BACKUP"` - Copies the original file to the backup file
10. `echo "Backup created: $BACKUP"` - Prints a success message

To use this script, you would:

1.  Save it to a file (for example, `backup.sh`)
2.  Make it executable: `chmod +x backup.sh`
3.  Run it: `./backup.sh important_document.txt`

The `chmod +x` command makes the file executable. The `chmod` command is used to change file permissions, and the `+x` flag adds execute permission.

You can also use octal notation with `chmod`:

- `chmod 755 filename` gives read, write, and execute permissions to the owner, and read and execute permissions to group and others
- `chmod 644 filename` gives read and write permissions to the owner, and read-only to group and others

Now that we've covered the basics of bash scripting, you have enough knowledge to start creating your own useful scripts to automate tasks on your system. Remember that the best way to learn is by doing - try creating small scripts to automate repetitive tasks you encounter in your daily work.

As you continue to explore bash, you'll discover more advanced features like arrays, functions, and more sophisticated flow control structures. But even with what we've covered here, you can already create scripts that will save you time and effort.

Happy scripting!
:::::::::::::::::::::::::::::::::::::::::
