<?php
error_reporting(-1);

require './helper.php';

switch ($_POST['action']) {
    case 'tmp_crop_image':
        $data = [

            'name' => $_FILES['image']['name'],

            'tmp_name' => $_FILES['image']['tmp_name'],

        ];

        $height = $_POST['img_height'];
        $width = $_POST['img_width'];

        $resized_image = image_resize($data, $height, $width);

        if ($resized_image !== false) {

            $res = $resized_image;
        } else {

            $res = [

                'status' => 0

            ];
        }
        echo json_encode($res);
        break;
    case 'manual_crop':
        $data = $_POST;

        $crop_res = final_crop($data);

        if ($crop_res !== false) {

            $res = [

                'status' => 1,

                'img_name' => $crop_res

            ];
        } else {

            $res = [

                'status' => 0

            ];
        }
        echo json_encode($res);
        break;
    case 'multi_image_crop':
        $height = $_POST['img_height'];
        $width = $_POST['img_width'];
        $files = $_FILES['files'];

        for ($i = 0; $i < count($files['name']); $i++) {

            $data = [
                'name' => $files['name'][$i],
                'tmp_name' => $files['tmp_name'][$i],
            ];

            list($img_width, $img_height) = getimagesize($data['tmp_name']);
            if ($img_width >= $width && $img_height >= $height) {
                $res = image_resize($data, $height, $width);
            } else {
                $res = false;
            }

            if ($res !== false) {
                $final_crop_data = [
                    'top' => 0,
                    'left' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'image' => $res['resizedimg'],
                    'maxWidth' => $res['maxWidth'],
                    'maxHeight' => $res['maxHeight'],
                    'dirPath' => $res['dir_path'],
                    'thumbFileName' => $res['thumbFileName'],
                    'ext' => $res['ext'],
                    'crop_image' => 1,
                ];
                final_crop($final_crop_data);
            }
        }

        break;
    default:
        throw new Exception('Not Authorized');
}
