# parse-torrent-name-php
Extract media information from torrent-like filename

This is a fork / port of the awesome libraries from [jzjzjzj](https://github.com/jzjzjzj/parse-torrent-name) and [divijbindlish](https://github.com/divijbindlish/parse-torrent-name)

Extract primary informations from a torrent downloaded filename into an information array.
This fork only support the extraction of few tokens, like the `title`, the `year`, `container`, `resolution`.
The primary use of it, is to make automatic rescue of informations on movie databases.

Example:
`The.Matrix.1999.1080p.BrRip.x264.YIFY.mp4`  
```
Array
(
    [year] => 1999
    [resolution] => 1080p
    [quality] => BrRip
    [codec] => x264
    [container] => mp4
    [title] => The Matrix
)
```

Demo:

```php
<?php
include("PTN.php");

$ptn=new PTN();
$infos=$ptn->parse("The.Matrix.1999.1080p.BrRip.x264.YIFY.mp4");
echo "This really cool movie is: ".$infos['title'];
?>
```
