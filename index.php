<!DOCTYPE html>
<html>
	 <meta name="viewport" content="width=device-width, initial-scale=1.0">
<body>
    <link rel="stylesheet" href="style.css">
<div class="bigBox">
<div class=innerText>
 <h1>Directory Listing</h1>
    <ul>

	      <?php
        // Replace 'path/to/your/directory' with the actual path
        $dir = 'path/to/your/directory';
        
        // Get the list of files and directories
        $files = scandir($dir);
        
        // Output each file/directory as a list item
        foreach($files as $file) {
            if ($file != '.' && $file != '..') {
                echo "<li><a href='$dir/$file'>$file</a></li>";
            }
        }
        ?>
    </ul>


<a href="./novels.html" class="linkBox"><h2 id="novels-and-other-literature">Novels and other literature</h2></a>
<p>I like to write stuff. I was, at some point ,wondering if I should
take up writing as a profession, and in that frenzy I was looking up for
places to put up my work. However, in due time I realized that I did not
want money out of writing. I just enjoy writing , and it gives me some
escape from other things in this world. I was at this time , quite
accustomed to programming , and also realized that github provides a
sort of “free” (pun intended) website hosting provided that you keep the
source open.</p>
<p>Well, when it comes to writing , I have nothing to hide , so I
thought I might as well put up a site where I would post my stuff even
if no one looked at them. After all , I was doing this for myself , not
to mention the fact that I don’t even have to pay to keep the website
running.</p>
</div>

</div>
<div class="bigBox">
<div class=innerText>
<a href="other_stuff.html" class="linkBox"><h2 id="other-stuff">Some Other Random Stuff</h2></a>
<p>I am an undergraduate student in physics. I am vaguely
acquainted with electronics , programming , simulations
and theoretical physics , and I have lower than normal grades in every
subject (thank you). I particularly like cameras and the various light
sensors that they use.</p>
</div>
</div>
<div class="bigBox">
<div class=innerText>
<a href="cs_stuff.html" class="linkBox"><h2 id="some-cs-stuff">Some CS Stuff</h2></a>

<p>Sometimes I would like to write about some CS stuff, maybe a bash
tutorial or something , I don’t know</p>
</div>
</div>
</body>
</html>
