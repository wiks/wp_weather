<?php

/**
Plugin Name: PogodaWiksEu
Version: 1.0
Description: Wtyczka 2.0 wyświetlająca pogodę na stronie
Author: WikS.eu
Author URI: http://wiks.eu
Plugin URI: http://pogoda.wiks.eu/about/widgets/
 */

// testy:
// ======
// wiks.pro-linuxpl.com/wp_test
// /domains/wiks.pro-linuxpl.com/public_html/wp_test/wp-content/plugins
// pogodawikseu@wiks.pro-linuxpl.com   qsoyFAQI
// /home/wiks/domains/wiks.pro-linuxpl.com/public_html/wp_test/wp-content/plugins/wp_pogodawikseu/

add_action( 'widgets_init', 'wiks_register_weather_widget' );


function wiks_register_weather_widget() {
    register_widget( 'PogodaWiksEu_Widget');
}


class HtmlWidgetu {
    
}


class PogodaWiksEu_Widget extends WP_Widget {
    
    /** wyświetla tablicę opcji
     *  classname to zarejestrowana klasa widgetu,
     *  description to opis widoczny pod obszarem widgetu w menu widok,
     *              przy ustawianiu widgetu
     *  w parent-constructor druga poz to opis pola w widokach j.w.
     */
    public function PogodaWiksEu_Widget() {
        
        $widget_ops = array(
            'classname' => 'PogodaWiksEu_Widget', 
            'description' => 'Pogodowy widżecik2.0 pobierający dane na podstawie tokena', 
        );
        parent::__construct( 'PogodaWiksEu_Widget', 'Pogoda i prognoza', $widget_ops );
        $this->url_img = plugins_url( 'img/', __FILE__ );
        $this->dir_img = plugin_dir_path(__FILE__).'img/';
        
        
        $subdir = 'speakers/';
//            $ppath = plugin_dir_path(__FILE__).$subdir;
        $ppath = $this->dir_img.$subdir;
        $this->imgs_speaker_url = $this->url_img.$subdir;
//            echo $ppath.'<br>';
        $this->imgs_speaker_list = $this->plugins_subdir_img_list($ppath);
        
        
    }    
    
    /** sprawdź, czy plik kończy się na
     * 
     * @param type $haystack
     * @param type $needle
     * @return type
     */
//    private function endsWith($haystack, $needle) {
//        
//        $length = strlen($needle);
//        return $length === 0 || (substr($haystack, -$length) === $needle);
//    }
    
    /** zwróć listę plików GIF w podanym podkatalogu
     * 
     * @return type
     */
//    private function plugins_subdir_img_list($ppath, 
//                                             $extension_string_or_list=
//                                                    array('gif', 
//                                                          'png')) {
//        
//        $files = array();
//        if ( $dir = opendir( $ppath ) ) {
//            while ( $file = readdir($dir) ) {
//                if ( $file != "." && $file != '..' ) {
//                    if ( !is_dir( $ppath . "/" . $file ) ) {
//                        // Hide files that start with a dot
//                        if ($file && $file['0'] != '.' ) {
//                            if(is_array($extension_string_or_list)) {
//                                foreach ($extension_string_or_list as $ext) {
//                                    if($this->endsWith($file, '.'.$ext)) {
//                                        $files[] = $file;
//                                    }
//                                }
//                            }else{
//                                if($this->endsWith($file, '.'.$extension_string_or_list)) {
//                                    $files[] = $file;        
//                                }
//                            }
//                        }
//                    }
//                }
//            }       
//            closedir($dir);                 
//        }
//        return $files;
//    }
    
    /** Formularz w kokpicie administratora (tam gdzie przeciągamy widgety)
     *  nazwy kluczy w defaults to odpowiedniki values
     *  w formularzu HTML, parse-args pobiera je z DB, wstawia domyślne jeśli brak
     * 
     * @param type $instance
     */
    public function form($instance) {
        
        $defaults = array(/*'tekst' => 'Mój tekst',*/
                          'now' => true,
                          '12h' => false,
                          'audio_on' => true,
                          'audio' =>'speaker_ico48.gif',
                          'forecast' => '2',
            );
        $instance = wp_parse_args( (array) $instance, $defaults);
        $tekst = $instance['tekst'];        
        ?>
        <p>
            <input type="hidden" name="developed_by" value="www.WikS.eu">
            <label>Pogoda:</label><br>
            <input type="checkbox" name="<?= $this->get_field_name('now');?>" value="now" <?= $instance['now']=='now' ? 'checked="checked"':''; ?> >pogoda teraz<br>
            <input type="checkbox" name="<?= $this->get_field_name('12h');?>" value="12h" <?= $instance['12h']=='12h' ? 'checked="checked"':''; ?> >prognoza na najbliższe godziny<br>
                        
            prognoza na kolejne dni
            <select name="<?= $this->get_field_name('forecast');?>">
            <?php for($i='0';$i<'4';$i++) {?>
                <option value="<?= $i; ?>" <?= $instance['forecast']==$i ? 'selected':''; ?> ><?= ($i=='0')?'nie pokazuj':$i; ?></option>
            <?php } ?>
            </select><br>

            <input type="checkbox" name="<?= $this->get_field_name('audio_on');?>" value="audio_on" <?= $instance['audio_on']!=NULL ? 'checked="checked"':''; ?>>dodaj nagrania<br>
            
            <?php            
//            print_r( $files );
            foreach ($this->imgs_speaker_list as $file) { ?>
                <input type="radio" name="<?= $this->get_field_name('audio');?>" value="<?= $file; ?>" <?= ($instance['audio']==$file?'checked="checked"':''); ?> >
                <img src="<?= $this->imgs_speaker_url.$file; ?>" style="width:18px;height:18px;">
                <br>
            <?php } ?>
        </p>
        <?php
    }
    
    /** zapis opcji widgetu
     * 
     * @param type $new_instance
     * @param type $old_instance
     * @return type
     */
//    public function update($new_instance, $old_instance) {
//        
//        $instance = $old_instance;
//        $instance['tekst'] = strip_tags( $new_instance['tekst']);
//        $instance['now'] = strip_tags( $new_instance['now']);
//        $instance['12h'] = strip_tags( $new_instance['12h']);
//        
//        $instance['forecast'] = strip_tags( $new_instance['forecast']);
//
//        $instance['audio'] = NULL;
//        $instance['audio_on'] = strip_tags( $new_instance['audio_on']);
//        if ($instance['audio_on'] == 'audio_on') {
//            $instance['audio'] = strip_tags( $new_instance['audio']);
//            if (!$instance['audio']) {
//                $instance['audio'] = $this->imgs_speaker_list['0'];
//            }
//        }
//        return $instance;        
//    }
    
    /** wytwórz liktę kluczy i ich opisów dla celów prognozy na najbliższe dni
     *  jeśli godzina b.wczesna to traktuj jakby jeszcze wczoraj wieczorem było
     * @return 
     */
//    private function produce_next4_key_ymd_list() {
//
//        $teraz = new Datetime();
//        $teraz_godzina = $teraz->format('H');
//        //echo $teraz_godzina."\n";
//        $as_jutro = $teraz->modify('+1 day');
//        $descr_index = '1';
//        if($teraz_godzina < '3') {
//            $as_jutro = $teraz;
//            $descr_index = '0';
//        }
//        $keys_array = array();
//        for($i='0';$i<'4';$i++) {
//            $descr = NULL;
//            if ($descr_index == '0') {
//                $descr = 'dziś';
//            }
//            elseif ($descr_index == '1') {
//                $descr = 'jutro';
//            }
//            $keys_array[] = array($as_jutro->format('Ymd'), $descr);
//            $as_jutro = $as_jutro->modify('+1 day');
//            $descr_index++;
//        }
//        return $keys_array;
//    }
    
    /** przygotuj nagłówek HTML pogody
     * 
     * @param type $jdata
     * @return string
     */
//    private function html_weather_header($jdata) {
//        // "place": "Poland, \u015awinouj\u015bcie", 
//        $my_html_content = '<strong>'
//                . 'Pogoda '
//                . '<a href="https://www.google.pl/maps/place/@53.9180332,14.2578572,15z">'
//                . 'w Świnoujściu'
//                . '</a>:'
//                . '</strong>'
//                . '<br>';
//        return $my_html_content;
//    }
    
    /** podaj html obrazka ikonki głośnika
     * 
     * @param type $instance
     * @return string
     */
//    private function html_img_speaker($instance) {
//        
//        // TODO:
//        // https://stackoverflow.com/questions/22962615/force-web-browser-to-play-files-rather-than-downloading
//        
//        $size = '18';
//        $html = '<img src="'.$this->imgs_speaker_url.$instance['audio'].'" '
//                . 'style="width:'.$size.'px;height:'.$size.'px;">';
//        return $html;
//    }
    
    /** generuj html pogody wg odebranych danych i opcji
     * 
     * @param type $jdata
     * @param type $instance
     * @return string
     */
//    private function html_weather_now_if_need($jdata, $instance) {
//        
//        $my_html_content = '';
//        if(isset($jdata["weather"]["now"]) && $instance['now']=='now') {
//            $html_head = '<u>o '.date_format(date_create($jdata["dt"]), "H:i").' :</u>';
//            if(isset($jdata["weather"]["now_audio"]) && $instance['audio']) {
//                $my_html_content .= 
//                        $this->html_img_speaker($instance).' '
//                        .'<a href="'.$jdata["weather"]["now_audio"].'">'.$html_head
//                        .'</a>';
//            }else{
//                $my_html_content .= $html_head;
//            }
//            if(isset($jdata["weather"]["now_icon_url"])) {
//                
//            }
//            $my_html_content .= '<br>'.$jdata["weather"]["now"].'<br>';
//        }
//        return $my_html_content;
//    }
    
    /** generuj html najbliższej prognozy wg odebranych danych i opcji
     * 
     * @param type $jdata
     * @param type $instance
     * @return string
     */
//    private function  html_weather_12h_if_need($jdata, $instance) {
//        
//        $my_html_content = '';
//        if(isset($jdata["weather"]["12h"]) && $instance['12h']=='12h') {
//            $html_head = '<u>najbliższe godziny:</u>';
//            if(isset($jdata["weather"]["12h_audio"]) && $instance['audio']) {
//                $my_html_content .= 
//                        $this->html_img_speaker($instance)
//                        .' '
//                        .'<a href="'.$jdata["weather"]["12h_audio"].'">'.$html_head
//                        .'</a>';
//            }else{
//                $my_html_content .= $html_head;
//            }
//            $my_html_content .= '<br>'.'<i>'.$jdata["weather"]["12h"].'</i><br>';
//        }
//        return $my_html_content;
//    }
    
    /** generuj html prognoz wg odebranych danych i opcji
     * 
     * @param type $jdata
     * @param type $instance
     * @return string
     */
//    private function  html_weather_forecast_if_need($jdata, $instance) {
//        
//        $my_html_content = '';
//        if(isset($jdata["forecast"]) && $instance['forecast']) {
//            $mydata = array();
//            foreach ($jdata["forecast"] as $key => $row1) {
//                if (strpos($a, 'audio') === false) {
//                    $mydata[$key] = array('text'=> $row1,
//                                          'audio_url' => NULL);
//                    if(isset($jdata["forecast"][$key.'_audio'])) {
//                        $mydata[$key]['audio_url'] = $jdata["forecast"][$key.'_audio'];
//                    }
//                }
//            }
//            if($mydata) {
//                $key_ymd_list = $this->produce_next4_key_ymd_list();
//                $lp_days = $instance['forecast']; // na ile dni ta prognoza
//                foreach ($key_ymd_list as $row2) {
//                    $key = $row2['0'];
//                    $head = $key;
//                    if($row2['1']) {
//                        $head = $row2['1'];
//                    }
//                    if (isset($mydata[$key]) && $lp_days > '0') {
//                        $my_head = '<u>'.$head.':</u>';
//                        if(isset($mydata[$key]["audio_url"]) && $instance['audio']) {
//                            $my_html_content .= 
//                                    $this->html_img_speaker($instance)
//                                    .' '
//                                    .'<a href="'.$mydata[$key]["audio_url"].'">'.$my_head
//                                    .'</a>';
//                        }else{
//                            $my_html_content .= $my_head;
//                        }
//                        $my_html_content .= '<br><i>'.$mydata[$key]['text'].'</i><br>';
//                        $lp_days--;
//                    }
//                }
//            }
//        }
//        return $my_html_content;
//    }
    
    /** podpisz mną
     * 
     * @param type $jdata
     * @return string
     */
//    private function html_powered_by($jdata) {
//        
//        $my_html_content .= '<p style="text-align:right;">'
//                . 'Powered by '
//                . '<a href="http://wiks.eu">'
//                . '&copy;2018 WikS.eu'
//                . '</a>'
//                . '</p>';
//        return $my_html_content;
//    }
    
    /** Wyświetlanie widgetu uzytkownikowi
     * 
     * @param type $args
     * @param type $instance
     */
    public function widget( $args, $instance) {
        
//        add_action( 'wp_print_footer_scripts', 'addFrontendJavascript' ); 
//        
//        extract($args);
////        echo $before_widget;
//        
//        /*if(!empty($instance['tekst'])) {
//            echo '<p>' . $instance['tekst'] . '</p>';
//        }*/
//        
//        $url = 'http://api.wiks.eu/weather';
//        $result = file_get_contents($url); // pytam i odbieram odpowiedź JSON
//        $jdata = json_decode($result, true);
//        // buduje skrypt JS - dane startowe
//        $my_js_settings = '<script>';
//        $my_js_settings .= 'var wiks_weather_now ="'.$instance['now'].'";';
//        $my_js_settings .= 'var wiks_weather_12h ="'.$instance['12h'].'";';
//        
//        $my_html_content = '';
//        $my_html_content .= $this->html_weather_header($jdata);
//        $my_html_content .= $this->html_weather_now_if_need($jdata, $instance);
//        $my_html_content .= $this->html_weather_12h_if_need($jdata, $instance);
//        $my_html_content .= $this->html_weather_forecast_if_need($jdata, $instance);
//        $my_html_content .= $this->html_powered_by($jdata);
//        
////        $my_html_content = '<img src="http://wiks.eu/weather/img/res/myimg_small_content.png">';
////        $my_html_content = '<img src="http://wiks.eu/weather/img/res/myimg_long_content.png">';
//        
//        $my_html_content = '<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
//            <script type="text/javascript" src="'.plugins_url( 'js/', __FILE__ ).'gcharts.js"></script>
//            <script type="text/javascript">
//            var dataa = null; 
//            var url = "http://wiks.eu/weather/arch/weather_json.php";
//            var myfunk = doit;
//            myajax(url, dataa, myfunk);
//            </script>
//            <div id="line_top_x">
//            </div>';
        
        $my_js_settings .= '</script>';
        
        $my_html_content = 'tresc widgetu';
        
        echo '<div class="widget" id="wiks_weather_content">'.$my_html_content.'</div>';
//        echo $my_js_settings;
//        echo $after_widget;        
    }
    
}

/** to było chwilę temu w klasie
 * 
 */
function addFrontendJavascript(){

    //$PLUGIN_PATH = 'http://blog.wiks.eu/wp-content/plugins/wiks_weather/';
    //echo '<script src="'.$PLUGIN_PATH.'js/wiks_weather.js"></script>';

//    wp_enqueue_script( 'wiks_main', plugins_url( '/js/wiks_weather.js' , __FILE__ ) );
        
// http://blog.wiks.eu/wp-content/plugins/wiks_weather/js/wiks_weather.js <--- tutaj jest
    
}


/*

https://benmarshall.me/wordpress-ajax-frontend-backend/
 powyżej fajnie AJAX w Wordpressie opisany


 */