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
    "The.Silence.Of.The.Lambs.1991.720p.BluRay.X264.YIFY.mp4",
    "The.Secret.Life.of.Pets.2016.HDRiP.AAC-LC.x264-LEGi0N",
    "Onward (2020) [1080p] [WEBRip] [5.1] [YTS.MX]",
    "2001: A Space Odyssey (1968) [BluRay] [1080p] [YTS.AM]"
];

foreach($filenames as $filename){
    $ptn = new PTN();
    echo $filename."\n";
    print_r($ptn->parse($filename));
    echo "\n\n";
}
?>
