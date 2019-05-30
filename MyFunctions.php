<?php

namespace App\Libraries {

    use App\FeeShipping;

    class MyFunctions
    {

        public function caculateShippingFee($level_customer=0, $store_vn=0, $weight=0, $type_shipping=0)
        {
            $shipping_fee = FeeShipping::where('level_customer', $level_customer)
                ->where('store_vn', $store_vn)
                ->where('type_shipping', $type_shipping)
                ->first();

            if($shipping_fee){
                $level_weight = '';
                if($weight > 0 && $weight <= 10){
                    $level_weight = '0_1-10';
                }elseif ($weight > 10 && $weight <= 20){
                    $level_weight = '10_1-20';
                }elseif ($weight > 20 && $weight <= 30){
                    $level_weight = '20_1-30';
                }elseif ($weight > 30 && $weight <= 200){
                    $level_weight = '30_1-200';
                }elseif ($weight > 200){
                    $level_weight = '>200';
                }

                return $weight * data_get($shipping_fee->fee, $level_weight);
            }
            return 0;

        }

        public function checkUploadDir()
        {
            //Kiem tra thu muc upload
            if (session()->has('userData.id')) {
                $dir = "upload/users/" . session('userData')['id'];
                if (!is_dir($dir)) {
                    $oldmask = umask(0);
                    @mkdir($dir, 0775, true);
                    umask($oldmask);
                }
                $cookie_value = str_random(16) . base64_encode("users/" . session('userData')['id']);
                setcookie('RF', $cookie_value, time() + (86400), "/"); // 86400 = 1 day
            }
        }

        public function content($text = '')
        {
            $result = '<style>p img{max-width: 100%}</style>' . $text;
            return $result;
        }

        //Performs a regex-texthighlight
        function textHighlight($search, $text, $highlightColor = '#FFFF22')
        {
            $text = str_replace($search, "<hl style='background: $highlightColor'>" . $search . "</hl>", $text);
            return $text;
        }

        //Get duong dan anh cu
        function get_old_dir_image($path = '')
        {
            $dir = dirname($path);
            return strstr($dir, 'images');
        }

        //Lay noi dung bang curl
        function get_content($url)
        {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

            $data = curl_exec($ch);
            curl_close($ch);

            return $data;
        }

        //lay url hien tai
        function url_current()
        {
            $pageURL = 'http';

            if (!empty($_SERVER['HTTPS'])) {
                if ($_SERVER['HTTPS'] == 'on') {
                    $pageURL .= "s";
                }
            }

            $pageURL .= "://";

            if ($_SERVER["SERVER_PORT"] != "80") {
                $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
            } else {
                $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
            }

            return $pageURL;
        }

        //Hàm bỏ dấu tiếng việt
        function removeSign($str)
        {
            $unicode = array(
                'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
                'd' => 'đ',
                'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
                'i' => 'í|ì|ỉ|ĩ|ị',
                'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
                'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
                'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
                'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
                'D' => 'Đ',
                'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
                'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
                'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
                'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
                'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
            );

            foreach ($unicode as $nonUnicode => $uni) {
                $str = preg_replace("/($uni)/i", $nonUnicode, $str);
            }
            return $str;
        }

        function urlTitle($str, $separator = '-', $lowercase = TRUE)
        {
            if ($separator === 'dash') {
                $separator = '-';
            } elseif ($separator === 'underscore') {
                $separator = '_';
            }

            $q_separator = preg_quote($separator, '#');

            $trans = array(
                '&.+?;' => '',
                '[^\w\d _-]' => '',
                '\s+' => $separator,
                '(' . $q_separator . ')+' => $separator
            );

            $str = strip_tags($this->removeSign($str));
            foreach ($trans as $key => $val) {
                $str = preg_replace('#' . $key . '#i' . (TRUE ? 'u' : ''), $val, $str);
            }

            $str = strtolower($str);

            return trim(trim($str, $separator));
        }

        function slug($str, $limit = null)
        {
            if ($limit) {
                $str = mb_substr($str, 0, $limit, "utf-8");
            }
            $text = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
            // replace non letter or digits by -
            $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
            // trim
            $text = trim($text, '-');
            $text = $this->removeSign($text);
            return $text;
        }

        function ordered_menu($array, $parent_id = 0)
        {
            $temp_array = array();
            foreach ($array as $element) {
                if ($element->parent_id == $parent_id) {
                    $element->subs = $this->ordered_menu($array, $element->cat_id);
                    $temp_array[] = $element;
                }
            }
            return $temp_array;
        }

        function youtube_id_from_url($url)
        {
            $url = urldecode(rawurldecode($url));
            preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $url, $matches);
            return $matches[1];
        }

        function addhttp($url)
        {
            if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
                $url = "http://" . $url;
            }
            return $url;
        }

        function isValidUsername($username)
        {
            if (preg_match('/^[a-zA-Z0-9]{4,}$/', $username)) { // for english chars + numbers only
                // valid username, alphanumeric & longer than or equals 4 chars
                return TRUE;
            }
            return FALSE;
        }

        function formWizard($step, $status)
        {
            if ($status < $step) {
                return 'disabled';
            } else {
                return 'done';
            }
        }

    }

}