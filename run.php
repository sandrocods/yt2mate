<?php
require_once 'lib/Class_downloader.php';

$YTdownload = new Ytdownload();

echo "\nEnter Link Video : ";
$answer_link = trim(fgets(STDIN));
$info = $YTdownload->Analyze($answer_link);
if ($info['Status'] === true) {
    echo "Title : ".$info['Data']['Title']."\n\n";
    for ($i=0; $i <count($info['Data']['Size']); $i++) {
        echo "[".$i."] Size : ".$info['Data']['Size'][$i]." | Quality : ".$info['Data']['f-quality'][$i]."\n";
    }
    echo "\nEnter number your choice : ";
    $answer_quality = trim(fgets(STDIN));
    $data = [
    1 => $info['Data']['k_data_vid'],
    2 => $info['Data']['k__id'],
    3 => $info['Data']['f-quality'][$answer_quality]
];
    // Download Default Quality
    // $download = $YTdownload->Getlink($data[1], $data[2]);
    $download = $YTdownload->Getlink($data[1], $data[2], $data[3]);
    print_r("\nLink Download : " .$download['Data']['Link']);
}else{
    echo $info['Data'];
}
