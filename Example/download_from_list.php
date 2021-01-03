<?php
require_once '../lib/Class_downloader.php';

$YTdownload = new Ytdownload();

echo "\nInput Link File : ";
$file = trim(fgets(STDIN));

echo "\nAuto Download [Y/n] : ";
$auto = trim(fgets(STDIN));

$lists = array_unique(explode("\n", str_replace("\r", "", file_get_contents($file))));

foreach ($lists as $link) {
    echo "Trying Download : ".$link."\n";
    $info = $YTdownload->Analyze($link);
    if ($info['Status'] === true) {
        echo "Title : ".$info['Data']['Title']."\n\n";

        /* Select Quality Disable use default quality 720p
        for ($i=0; $i <count($info['Data']['Size']); $i++) {
            echo "[".$i."] Size : ".$info['Data']['Size'][$i]." | Quality : ".$info['Data']['f-quality'][$i]."\n";
        }
        echo "\nEnter number your choice : ";
        $answer_quality = trim(fgets(STDIN)); 
        */

        $data = [
            1 => $info['Data']['k_data_vid'],
            2 => $info['Data']['k__id'],
            //3 => $info['Data']['f-quality'][$answer_quality]
        ];

        // Download Default Quality 720p
        $download = $YTdownload->Getlink($data[1], $data[2] , '720p');
        print_r("\nLink Download : " .$download['Data']['Link']."\n\n");
        $check_extension = get_headers ( $download['Data']['Link'] );
        $check_extension = ($check_extension[3] == 'Content-Type: video/mp4') ? $ext = '.mp4' : $ext = '.mp3'; 
        $auto = (strtoupper($auto) == 'Y') ? file_put_contents(dirname(__FILE__) . '/'.$info['Data']['Title'].$ext, fopen($download['Data']['Link'], 'r')) : '' ;
    } else {
        echo $info['Data'];
    }
}
