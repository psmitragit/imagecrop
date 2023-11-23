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
    default:
        throw new Exception('Not Authorized');
}
