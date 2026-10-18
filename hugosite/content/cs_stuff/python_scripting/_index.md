---
title: "Python Scripting"
date: 2026-10-18T00:00:00Z
---

## Table of Contents

1.  [About the Interpreter](#heading-1)
2.  [About the REPL](#heading-2)
3.  [The Interpreter as a File Manager](#heading-3)
4.  [Running Python Scripts](#heading-4)
5.  [The PATH to Imports](#heading-5)
6.  [Simple Logic in Python](#heading-6)
7.  [Bang! Bang! She-Bang!](#heading-7)

## About the Interpreter {#heading-1}

If you're reading this, chances are you have already met a shell, either through the [bash scripting guide](/cs_stuff/bash_scripting/) or by accident. A shell takes text, works out what each word means, and does something with it. Python works in much the same way, which is exactly why the two fit together so well. The difference is not really about how the machine reads the text, but about what the words are allowed to mean.

Let's start from the same place. When you type `ls` into a terminal, something has to exist on your disk as a program before that command can do anything. That something is usually `/bin/ls` (or wherever your `PATH` says). The shell finds the file, hands it to the operating system, and the operating system starts it up.

Python is arranged differently. There is no `python` sitting in `/bin` waiting to be one command or another. Instead there is a single program, the interpreter, and it does one job: it reads a stream of Python source code and executes it. Everything else, every list comprehension and every file operation and every web framework you have ever used, is built out of that one simple loop. Read a chunk of code. Work out what it means. Do it. Repeat.

So when people say a language is "interpreted", what they really mean is that there is this extra program sitting between your text and the machine. With a compiled language like C, you hand your source to a compiler, and what comes out the other end is a binary that the operating system can run directly, with nothing in between. With Python, your source is handed to the interpreter every single time, and the interpreter is the thing that actually executes it.

The practical upshot of this is that Python is, in a very literal sense, one level of indirection away from the hardware where bash would have been zero. When you run a bash script, the shell is doing the interpreting. When you run a Python script, the interpreter is doing the interpreting. In both cases something is reading text and deciding what to do with it, and in both cases that something is just a program that happens to be very good at one job.

There is one wrinkle worth knowing about, because it explains a folder you have probably seen and wondered about. Before executing your code, the interpreter compiles it into an intermediate form called bytecode, and when that code comes from an imported module, it caches that bytecode on disk so it does not have to do the work a second time. That cache is what the `__pycache__` directory is, and it is full of `.pyc` files named things like `mymodule.cpython-312.pyc`.

Worth being precise about which file does not get cached, because it surprises people: the script you run directly is never cached. If you run `python3 runner.py`, the `__pycache__` that appears next to it will contain an entry for every module `runner.py` imported, and none for `runner.py` itself. The main script is recompiled every single run, so editing it takes effect immediately with nothing to clean. This is not a leftover from a build step in the way that `.o` files in a C project are. You are perfectly safe deleting `__pycache__`, and Python will regenerate it the next time you import anything.

Finally, worth mentioning that "the Python interpreter" is not one specific program. CPython is the reference implementation, written in C, and it is what you get when you run `python3` on most Linux distributions. PyPy is a separate implementation that traces the code as it runs and tends to be considerably faster for long-running numeric work. Both accept the same syntax, so everything in this guide applies to either. You can check which one you have:

```
$ python3 -c "import sys; print(sys.implementation.name)"
cpython
```

## About the REPL {#heading-2}

Since Python is a program that reads text and executes it, it stands to reason that you can just hand it text directly and watch what happens. That is exactly what happens when you run `python3` with no arguments:

```
$ python3
Python 3.12.4 (main, Jun  7 2024, 09:30:08) [GCC 13.2.0] on linux
Type "help", "copyright", "credits" or "license" for more information.
>>>
```

That `>>>` is the prompt, and what you are looking at is the REPL, which stands for Read, Evaluate, Print, Loop. You read a line, Python evaluates it, prints the result if there is one, and gives you the prompt back. It is the Python equivalent of the interactive bash shell we spent the first two sections of the other guide on, and it is genuinely one of the best things about the language.

If you are following along with the bash guide, you might expect Python to work the way your shell does when you type things into it. Try typing something and see:

```
>>> 2 + 2
4
>>> print("hello")
hello
```

Notice what is different. In a shell, a bare word is looked up as a command, an argument, or a filename, and if the shell cannot make sense of it you get an error. In Python, a bare word is either a name you have already defined or a name Python will try to look up somewhere else. `print` is not a command, it is a function that happens to be built in, and the parentheses are what make it a function call rather than a word.

Three builtins are worth memorizing early, because between them they let you interrogate the interpreter the way you would interrogate a shell:

```
>>> type(2 + 2)
<class 'int'>
>>> dir(print)
['__call__', '__class__', ...]
>>> help(print)
```

`type` tells you what something is, `dir` lists what an object can do, and `help` prints the documentation for it. If you ever get lost, these three will get you unstuck faster than any documentation site.

Why does the REPL matter so much? Because it turns programming into a conversation. You can try an idea, see immediately whether it works, poke at it, and only then commit it to a file. Every idea in this guide was checked in a REPL before it was written down here.

One small piece of advice before we move on. The REPL is for exploring. Once you have something that works, move it into a file, because a REPL session is throwaway. Python will not save it for you, and retyping forty lines to fix a typo is a good way to lose patience with a language that is otherwise quite pleasant to work in.

## The Interpreter as a File Manager {#heading-3}

The single biggest difference between a shell and Python is that a shell treats files as something you stream through, while Python treats them as something you read into memory. In bash, a file is a thing you `cat`, `grep`, or redirect into another command. In Python, a file is a thing you open, read into a string or a list, and then work with as an ordinary object.

That makes Python a much better file manager than the shell is, and it is worth spending a section on, because the tooling for it is part of the standard library and needs no installation at all. The modern way to do this is with `pathlib`, which represents a path as an object rather than as a string you have to keep chopping up.

```python
from pathlib import Path

home = Path.home()
print(home)
```

`Path.home()` is the equivalent of running `pwd` to check where you are, except it gives you your home directory instead of your current one. For the current directory:

```python
print(Path.cwd())
```

To list what is in a directory, the shell's `ls` becomes a generator you can loop over:

```python
for entry in Path.cwd().iterdir():
    print(entry.name)
```

This is worth pausing on, because it is the first real taste of how Python differs from bash. `iterdir` does not build a list and hand it to you, it yields one entry at a time. If a directory has ten thousand files in it, you can start printing the first one before the last one has even been looked at. In the shell, everything has to finish before you see anything.

Making a directory is `mkdir`:

```python
Path("new_directory").mkdir()
```

The equivalent of `mkdir -p`, which creates parent directories as needed, is `parents=True`:

```python
Path("parent/child/grandchild").mkdir(parents=True)
```

You will notice that Python will not let you clobber an existing directory, which is the same safety the shell gives you. If you want the shell's behaviour instead, say so explicitly:

```python
Path("new_directory").mkdir(exist_ok=True)
```

Deleting things is where the shell and Python differ most sharply, and it is worth being deliberate about it. To remove a file:

```python
Path("filename").remove()
```

Wait, that is not right either. To remove a file it is `unlink`, which is a slightly odd name inherited from the underlying system call, but it is the one you have to learn:

```python
Path("filename").unlink()
```

`unlink` refuses to remove a directory, just as `rm` refuses to. To remove a directory:

```python
Path("directory_name").rmdir()
```

And here is the important part. There is no equivalent of `rm -rf`. Python will not give you a single call that recursively destroys a tree, because the ergonomics that make `rm -rf` convenient in a shell are exactly the ergonomics that make it dangerous. When you genuinely need to delete a directory tree, you say so, out loud, in the only way Python offers:

```python
import shutil

shutil.rmtree("directory_name")
```

`rmtree` is the whole of it. Notice that it is spelled differently from every other method on `Path`, and it is not a method at all, because it is not part of `pathlib`. That is deliberate on the part of the language designers. The dangerous operation should look different from the safe ones, and it does.

Renaming and moving are methods, because they are perfectly safe and cannot make you lose data:

```python
Path("old_name").rename("new_name")
Path("file.txt").rename("/path/to/destination/file.txt")
```

That last one doubles as the shell's `mv`, because `rename` does not care whether the destination is in the same directory or not. If you want to copy instead, `pathlib` only gives you `copy` for single files, and for trees you go back to `shutil`:

```python
import shutil

shutil.copy("source_file", "destination_file")
shutil.copytree("source_directory", "destination_directory")
```

`shutil.copy` copies the contents of a file. `shutil.copy2`, which we will meet again at the end of this guide, does the same thing but also preserves the timestamps and permissions, which matters more often than you would think if you are copying config files around.

## Running Python Scripts {#heading-4}

Now that we can ask Python questions and poke at files, let us talk about writing actual programs and running them.

The simplest possible program is one line long:

```python
print("Hello, World!")
```

Saved as `hello.py`, you run it like this:

```
$ python3 hello.py
Hello, World!
```

Notice that `python3 hello.py` is not the shell running Python, it is the shell starting the interpreter and handing it a filename. The shell's entire role is to find the interpreter and pass the argument along. Everything after that is Python's problem.

Those extra arguments are worth understanding, because every Python script receives them. They arrive in a list called `sys.argv`:

```python
import sys

print("Arguments:", sys.argv)
```

Run it with some arguments:

```
$ python3 greet.py Alice 42
Arguments: ['greet.py', 'Alice', '42']
```

The first element is the name of the script itself, not your argument, and every argument arrives as a string, no matter what it looks like. That last part catches nearly everyone out eventually. The `42` above is the string `"42"`, not the integer `42`.

This is why almost every real Python script reaches for `argparse` instead of reading `sys.argv` directly. `argparse` is in the standard library, it gives you `--help` for free, it converts your strings into real types, and it produces error messages that a human can read:

```python
import argparse

parser = argparse.ArgumentParser(description="Greet someone.")
parser.add_argument("name", help="who to greet")
parser.add_argument("times", type=int, default=1, help="how many times")
args = parser.parse_args()

for _ in range(args.times):
    print(f"Hello, {args.name}!")
```

That `type=int` is the whole reason to bother. You asked for an integer and you got one.

Every program that runs on a unix-like system is expected to do one thing when it finishes: report success or failure through its exit code. Zero means success, anything else means failure. In Python you control this with `sys.exit`, which raises an exception that the interpreter catches and turns into a status code:

```python
import sys

if len(sys.argv) < 2:
    print("Usage: greet.py name", file=sys.stderr)
    sys.exit(1)

print(f"Hello, {sys.argv[1]}!")
sys.exit(0)
```

Two details in there are worth pointing out. The `file=sys.stderr` sends the error to standard error rather than standard output, which is the convention that lets you pipe a program's results somewhere while still seeing its complaints on your screen. And the explicit `sys.exit(0)` at the end is not strictly necessary, since falling off the end of a script exits with zero anyway, but it makes the contract with the shell obvious.

There are a few other ways to run Python code that are worth knowing. The `-c` flag takes a program as a string, which is handy for a quick one-liner or for poking at something from a shell script:

```
$ python3 -c "import sys; print(sys.platform)"
linux
```

The `-m` flag runs a module as if it were a script, but with the module's directory added to the import path first. That is why `python3 -m pip install ...` is the recommended way to invoke pip, and why `python3 -m pytest` is the recommended way to run pytest. It guarantees you are using the copy of the library that belongs to the same interpreter you just named.

## The PATH to Imports {#heading-5}

We talked about how the shell finds commands by searching a list of directories called `PATH`. Python has exactly the same mechanism, it just calls it something else and stores it in a list rather than a colon separated string.

That list is `sys.path`. When you write an import, Python walks through it in order and uses the first match:

```
$ python3 -c "import sys; print('\n'.join(sys.path))"
```

You will see the directory of the script you are running, then a pile of directories belonging to your Python installation, and then a `site-packages` directory. `site-packages` is where `pip` puts things, and it is why `import requests` works on a machine where nobody has ever heard of `requests`.

Adding a directory to it works much like modifying `PATH`:

```python
import sys

sys.path.append("/new/directory")
```

Except that, unlike `export PATH=...`, this only affects the one program you did it in. If you want the change to stick for everything, you set an environment variable called `PYTHONPATH` before you run:

```
$ PYTHONPATH=/new/directory python3 myscript.py
```

Now here is the security story, and it is a direct parallel to the `./myscript` rule from the bash guide, so it is worth reading carefully.

When you run `python3 myscript.py`, Python does not put your current working directory at the front of the import path. It puts the directory containing `myscript.py`. If you are sitting in a directory full of other people's downloads, and that directory contains a file called `random.py`, then any import of `random` anywhere in your script will silently pick up that file instead of the real one from the standard library. The same goes for `json.py`, `email.py`, `socket.py`, and a hundred other names you would never think to check for.

This is why the advice that everyone repeats, and nobody explains, is to run your scripts by filename and not by bare name. From the bash guide:

```
$ python3 myscript.py
```

is safe, and this is the dangerous version:

```
$ python3 -c "import mymodule"
```

That one does put your current directory at the front of the path, because that is where you presumably meant to import from. If you are in a directory you do not trust, you can turn that off with the `-P` flag:

```
$ python3 -P -c "import sys; print(sys.path[0])"
```

Python 3.11 added `-P`, along with the `PYTHONSAFEPATH` environment variable, precisely so that this behaviour could be turned off. It is not on by default, which is a decision you may or may not agree with, but it is worth knowing that the switch exists.

The other half of this story is virtual environments. When you `pip install` something, it goes into a `site-packages` directory, and by default there is one of those shared by every user on the machine. That means two projects needing two different versions of the same library will fight, and one of them will lose.

The fix is to give each project its own set of installed packages:

```
$ python3 -m venv .venv
$ source .venv/bin/activate
```

The first command creates a directory called `.venv` containing a complete but lightweight copy of the interpreter, with its own private `site-packages`. The second command puts that copy at the front of your `PATH`, which means from now on, in this shell, `python3` and `pip` refer to the copies inside `.venv` and nothing else can see them.

The prompt changes to tell you which environment you are in:

```
(.venv) $ python3 -m pip install requests
```

When you are done, `deactivate` puts your old `PATH` back. And because this is just `PATH` manipulation underneath, everything you learned about `PATH` in the bash guide applies here unchanged.

## Simple Logic in Python {#heading-6}

So far we have looked at running programs one after the other. The real power of any language comes from making decisions and repeating actions based on conditions, and this is where Python's programming capabilities shine.

The most common conditional is the `if` statement, and its basic syntax is refreshingly simple compared to bash, because there is no `then` and no `fi` and nothing to remember about where the block ends:

```python
if condition:
    # commands to run if condition is true
    pass
elif other_condition:
    # commands to run if the first one was false
    pass
else:
    # commands to run if everything was false
    pass
```

Indentation is what defines the block. In bash you had braces and semicolons and `fi` to keep track of; in Python the indentation after the colon is the block, full stop. This is enforced by the interpreter rather than by convention, which means you cannot get it subtly wrong without being told.

Let us look at a practical example that checks whether a file exists:

```python
from pathlib import Path

path = Path("myfile.txt")

if path.is_file():
    print("myfile.txt exists")
elif path.is_dir():
    print("myfile.txt is a directory")
else:
    print("myfile.txt does not exist")
```

Every object in Python carries the methods that describe what it is, so the tests you would write with `-f` and `-d` in bash are methods on the path itself. `is_file`, `is_dir`, `exists`, and `is_symlink` cover most of what you need.

Now, the condition does not have to be a yes-or-no question in the strict sense. Python has a concept called truthiness, where anything can be interpreted as true or false, and this is the single most useful thing to internalize about Python's conditionals. The rule is short:

- Empty things are false. That means `""`, `0`, `0.0`, `[]`, `{}`, `set()`, `()` and `None`.
- Everything else is true.

So a full set of tests collapses into a handful of comparisons:

```python
if not name:          # name is empty or None
    print("no name given")

if not items:         # items is an empty list
    print("nothing to do")

if count:             # count is non-zero
    print("we have some")
```

If you want to be explicit about an object rather than rely on it being empty, `len()` is the clearest way for anything sized:

```python
if len(name) == 0:
    print("no name given")
```

This also tells you what the danger is. In Python, a value of `0` is a perfectly ordinary number, and so is an empty string. A test like `if count:` silently treats "we counted zero of them" as "we did not count", which is sometimes what you want and occasionally is not. When the difference matters, compare explicitly.

Conditions can be combined with `and`, `or`, and `not`, which work the way `&&`, `||`, and `!` do in bash:

```python
if path.is_file() and path.stat().st_size > 0:
    print("file exists and is not empty")
```

There is one thing here that has no equivalent in bash at all. Python allows chaining comparisons, which is how humans naturally phrase a range:

```python
if 0 <= score <= 100:
    print("valid score")
```

That is not sugar for `0 <= score and score <= 100`, although it behaves identically. It evaluates the middle expression once, which matters when it is a function call that is expensive, or when it has side effects.

Another useful construct is the `match` statement, which is Python's answer to the shell's `case` statement. You write it as `match` followed by an indented block of `case` lines, and it dispatches on the shape and value of a single expression:

```python
from pathlib import Path


def describe(filename: str) -> str:
    match Path(filename).suffix:
        case ".txt":
            return "This is a text file"
        case ".jpg" | ".jpeg":
            return "This is a JPEG image"
        case ".mp3":
            return "This is an MP3 audio file"
        case _:
            return f"Unknown file type: {filename}"
```

The `|` at the end of a `case` line means "or", exactly as it does in a `case` statement in bash. The `case _:` line is the catch-all, matching anything that did not match anything else. The difference from bash is that `match` is a real expression rather than a command, so it can be assigned to a variable or returned from a function, and the cases are checked by structure rather than by string substitution.

One last piece of control flow, and it is the one that changes how you write everything else. The `for` loop in Python does not run "up to some number". It walks over whatever you hand it, one item at a time:

```python
for entry in Path.cwd().iterdir():
    print(entry.name)
```

And the `while` loop is for when you genuinely do not know how many times you need to go around:

```python
while True:
    line = input("Say something (or 'quit' to stop): ")
    if line == "quit":
        break
    print("You said:", line)
```

`break` leaves the loop entirely. There is also `continue`, which skips to the next iteration, and that is the same in Python as it is in bash.

## Bang! Bang! She-Bang! {#heading-7}

We have been running Python by asking the interpreter to read a file for us, but how do we make reusable scripts that we can run over and over, exactly like we did at the end of the bash guide? The answer is the same, and it is the same first line.

A Python script is simply a text file containing Python code. To make it runnable on its own, you do two things:

1. Add a shebang line at the very beginning of the file
2. Make the file executable with the `chmod` command

The shebang line looks like this:

```python
#!/usr/bin/env python3
```

This line must be the very first line in the file, with no spaces or characters before it. It tells the operating system which interpreter to use. If you read the bash guide, you will remember that the shebang there looked like `#!/usr/bin/env bash`, and you may remember that the list of alternative interpreters mentioned `#!/usr/bin/env python3` with the aside that it would not, in that case, be a bash script. Well. That is the moment.

Using `/usr/bin/env` rather than a hardcoded `/usr/bin/python3` is preferred for the same reason it was preferred in bash: it finds the interpreter using your `PATH`, so the script keeps working on machines where Python lives somewhere else, which in practice means it keeps working after you have activated a virtual environment.

Let us write a script. Since the bash guide ended with a script that makes a timestamped backup of a file, let us write the Python version of the same thing, and see how much of it is recognisable:

```python
#!/usr/bin/env python3

"""Simple backup script."""

import argparse
import shutil
import sys
from datetime import datetime
from pathlib import Path


def main() -> int:
    parser = argparse.ArgumentParser(description="Back up a file with a timestamp.")
    parser.add_argument("filename", type=Path, help="the file to back up")
    args = parser.parse_args()

    source = args.filename
    if not source.is_file():
        print(f"Error: File '{source}' not found", file=sys.stderr)
        return 1

    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    backup = source.with_name(f"{source.name}.{timestamp}.bak")

    shutil.copy2(source, backup)
    print(f"Backup created: {backup}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
```

Let us break down what this script does, in the same spirit as the breakdown at the end of the bash guide:

1. `#!/usr/bin/env python3` - The shebang line, telling the system to find Python 3 on the `PATH`
2. `"""Simple backup script."""` - A docstring. The three quotes at the top of a file are a convention, not a requirement, but they are what `help(mymodule)` will print, so they are worth writing
3. `import argparse`, `import shutil`, `import sys` - Three modules from the standard library. That all three ship with Python is the point: a working script needs nothing installed
4. `from pathlib import Path` - Importing a single name from a module, rather than the whole module
5. `def main() -> int:` - Defining a function. The `-> int` is a type hint, which is optional and completely ignored at runtime, but which tells a human, and any type checker, what the function gives back
6. `parser.add_argument("filename", type=Path)` - Declaring an argument, converted to a `Path` rather than left as a string
7. `if not source.is_file():` - The same existence test we used a few sections ago, this time sending the error to `sys.stderr`
8. `datetime.now().strftime("%Y%m%d_%H%M%S")` - The identical timestamp format we used in bash
9. `shutil.copy2(source, backup)` - Copying the file, preserving its timestamps and permissions
10. `return 0` - Reporting success. Returning `1` above it reported failure, and the shell turned that into an exit code
11. `if __name__ == "__main__":` - The guard that stops the script from running when something else imports it
12. `sys.exit(main())` - Running `main`, then exiting with whatever it returned

That last one deserves a moment, because it has no equivalent in bash. When Python runs a file, it sets a built-in variable called `__name__` to `"__main__"`. When a file is imported by another file, `__name__` becomes the name of the module instead. So `if __name__ == "__main__":` asks "am I being run as a program right now, or was I pulled in by something else?", and it lets you keep your functions importable and testable without them firing the moment they are touched.

Notice also that the error and the success message go to different places. `print` writes to standard output, which is what a shell pipes or redirects. The `file=sys.stderr` on the error writes to standard error, which is where complaints belong. Try it and you will see it:

```
$ ./backup.py important_document.txt
Backup created: important_document.txt.20261018_141530.bak
$ ./backup.py missing_file.txt
Error: File 'missing_file.txt' not found
$ echo $?
1
```

To use the script, the steps are identical to the bash version. Save it as `backup.py`, make it executable, run it:

```
$ chmod +x backup.py
$ ./backup.py important_document.txt
```

The `chmod +x` command is the same one from the bash guide, and the permissions it sets mean the same thing. A file that is executable gets one more bit set in its mode, and that bit is what the kernel checks when it looks at the shebang line and decides to run the file rather than hand it to you as a document.

What changed between the two versions is worth stating plainly, because it is the difference between the languages in one comparison. The bash version read `$1`, checked `[ ! -f "$1" ]`, built a filename by pasting strings together, and called `cp`. The Python version reads an argument object, calls a method on it to ask what it is, builds a filename with `with_name` so the directory part cannot be mangled by accident, and calls `shutil`. The logic is the same logic. What you are writing down is different, because in Python the things you operate on have the answers attached to them.

You now have enough to start writing your own. The best way to learn any of this is by doing, so take a repetitive task you do by hand and write four lines of Python to do it instead. Most people find that the interpreter being a real REPL, sitting right there in the terminal, makes trying things far less frightening than it looks on paper.

As you go further you will meet decorators, context managers, generators, async, and a great deal of tooling that will change how you write the simple parts. But what is here is enough to write real scripts that save you time, which is the entire point of the exercise.

Happy scripting!