<?php
include("../PTN/PTN.php");

$filenames = [
    "Class.of.1984.1982.1080p.BluRay.x264.YIFY.mp4",
    "V.For.Vendetta.2006.1080p.BrRip.x264.YIFY.mp4",
    "Fight.Club.1999.720p.BrRip.x264.YIFY.mp4",
    "The.Matrix.1999.1080p.BrRip.x264.YIFY.mp4",
    "WarGames.1983.720p.BluRay.x264-[YTS.AG].mp4",
    "Hannibal.2001.720p.BluRay.x264.YIFY.mp4",
    "The.School.Of.Rock.2003.720p.BluRay.YIFY.mp4",
    "It.Chapter.Two.2019.720p.BluRay.x264-[YTS.LT].mp4",
    "The.Silence.Of.The.Lambs.1991.720p.BluRay.X264.YIFY.mp4"
];

foreach($filenames as $filename){
    $ptn = new PTN();
    print_r($ptn->parse($filename));
}
?>
