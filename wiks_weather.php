<?php

/**
Plugin Name: WikS_weather
Version: 1.0
Description: Wtyczka wyświetlająca pogodę na stronie
Author: WikS.eu
Author URI: http://www.wiks.eu
Plugin URI: http://www.wiks.eu/wp/widgets/weather/
 */

add_action( 'widgets_init', 'wiks_register_weather_widget' );



function wiks_register_weather_widget() {
    register_widget( 'Weather_Widget');
}


class HtmlWidgetu {
    
}


class Weather_Widget extends WP_Widget {
    
    /** wyświetla tablicę opcji
     *  classname to zarejestrowana klasa widgetu,
     *  description to opis widoczny pod obszarem widgetu w menu widok,
     *              przy ustawianiu widgetu
     *  w parent-constructor druga poz to opis pola w widokach j.w.
     */
    public function Weather_Widget() {
        
        $widget_ops = array(
            'classname' => 'Weather_Widget', 
            'description' => 'Pogodowy widżecik', 
        );
        parent::__construct( 'Weather_Widget', 'Pogoda i prognoza', $widget_ops );
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
    private function endsWith($haystack, $needle) {
        
        $length = strlen($needle);
        return $length === 0 || (substr($haystack, -$length) === $needle);
    }
    
    /** zwróć listę plików GIF w podanym podkatalogu
     * 
     * @return type
     */
    private function plugins_subdir_img_list($ppath, 
                                             $extension_string_or_list=
                                                    array('gif', 
                                                          'png')) {
        
        $files = array();
        if ( $dir = opendir( $ppath ) ) {
            while ( $file = readdir($dir) ) {
                if ( $file != "." && $file != '..' ) {
                    if ( !is_dir( $ppath . "/" . $file ) ) {
                        // Hide files that start with a dot
                        if ($file && $file['0'] != '.' ) {
//                            if ($extension_string_or_list) {
                            if(is_array($extension_string_or_list)) {
                                foreach ($extension_string_or_list as $ext) {
                                    if($this->endsWith($file, '.'.$ext)) {
                                        $files[] = $file;
                                    }
                                }
                            }else{
                                if($this->endsWith($file, '.'.$extension_string_or_list)) {
                                    $files[] = $file;        
                                }
                            }
//                            }else{
//                                $files[] = $file;
//                            }
                        }
                    }
                }
            }       
            closedir($dir);                 
        }
//        print_r($files);
        return $files;
    }
    
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
    public function update($new_instance, $old_instance) {
        
        $instance = $old_instance;
        $instance['tekst'] = strip_tags( $new_instance['tekst']);
        $instance['now'] = strip_tags( $new_instance['now']);
        $instance['12h'] = strip_tags( $new_instance['12h']);
        
        $instance['forecast'] = strip_tags( $new_instance['forecast']);

        $instance['audio'] = NULL;
        $instance['audio_on'] = strip_tags( $new_instance['audio_on']);
        if ($instance['audio_on'] == 'audio_on') {
            $instance['audio'] = strip_tags( $new_instance['audio']);
            if (!$instance['audio']) {
                $instance['audio'] = $this->imgs_speaker_list['0'];
            }
        }
        return $instance;        
    }
    
    /** wytwórz liktę kluczy i ich opisów dla celów prognozy na najbliższe dni
     *  jeśli godzina b.wczesna to traktuj jakby jeszcze wczoraj wieczorem było
     * @return Array (
            [0] => [20180507, 'dziś']
            [1] => [20180508, 'jutro']
            [2] => [20180509, 'po jutrze']
            [3] => [20180510, '']
        )
     */
    private function produce_next4_key_ymd_list() {

        $teraz = new Datetime();
        $teraz_godzina = $teraz->format('H');
        //echo $teraz_godzina."\n";
        $as_jutro = $teraz->modify('+1 day');
        $descr_index = '1';
        if($teraz_godzina < '3') {
            $as_jutro = $teraz;
            $descr_index = '0';
        }
        $keys_array = array();
        for($i='0';$i<'4';$i++) {
            $descr = NULL;
            if ($descr_index == '0') {
                $descr = 'dziś';
            }
            elseif ($descr_index == '1') {
                $descr = 'jutro';
            }
            $keys_array[] = array($as_jutro->format('Ymd'), $descr);
            $as_jutro = $as_jutro->modify('+1 day');
            $descr_index++;
        }
//        print_r($keys_array);
        return $keys_array;
    }
    
    /** przygotuj nagłówek HTML pogody
     * 
     * @param type $jdata
     * @return string
     */
    private function html_weather_header($jdata) {
        // "place": "Poland, \u015awinouj\u015bcie", 
        $my_html_content = '<strong>'
                . 'Pogoda '
                . '<a href="https://www.google.pl/maps/place/@53.9180332,14.2578572,15z">'
                . 'w Świnoujściu'
                . '</a>:'
                . '</strong>'
                . '<br>';
        return $my_html_content;
    }
    
    /** podaj html obrazka ikonki głośnika
     * 
     * @return string
     */
    private function html_img_speaker($instance) {
        
        // TODO:
        // https://stackoverflow.com/questions/22962615/force-web-browser-to-play-files-rather-than-downloading
        
//        $dir_img = plugins_url( 'img/', __FILE__ );
        $size = '20';
        $html = '<img src="'.$this->imgs_speaker_url.$instance['audio'].'" '
                . 'style="width:'.$size.'px;height:'.$size.'px;">';
        return $html;
    }
    
    /** generuj html pogody wg odebranych danych i opcji
     * 
     * @param type $jdata
     * @param type $instance
     * @return string
     */
    private function html_weather_now_if_need($jdata, $instance) {
        
        $my_html_content = '';
        if(isset($jdata["weather"]["now"]) && $instance['now']=='now') {
            $html_head = '<u>o '.date_format(date_create($jdata["dt"]), "H:i").' :</u>';
            if(isset($jdata["weather"]["now_audio"]) && $instance['audio']) {
                $my_html_content .= 
                        $this->html_img_speaker($instance)
                        .' '
                        .'<a href="'.$jdata["weather"]["now_audio"].'">'.$html_head
                        .'</a>';
            }else{
                $my_html_content .= $html_head;
            }
            if(isset($jdata["weather"]["now_icon_url"])) {
                
            }
            $my_html_content .= '<br>'.$jdata["weather"]["now"].'<br>';
        }
        return $my_html_content;
    }
    
    /** generuj html najbliższej prognozy wg odebranych danych i opcji
     * 
     * @param type $jdata
     * @param type $instance
     * @return string
     */
    private function  html_weather_12h_if_need($jdata, $instance) {
        
        $my_html_content = '';
        if(isset($jdata["weather"]["12h"]) && $instance['12h']=='12h') {
            $html_head = '<u>najbliższe godziny:</u>';
            if(isset($jdata["weather"]["12h_audio"]) && $instance['audio']) {
                $my_html_content .= 
                        $this->html_img_speaker($instance)
                        .' '
                        .'<a href="'.$jdata["weather"]["12h_audio"].'">'.$html_head
                        .'</a>';
            }else{
                $my_html_content .= $html_head;
            }
            $my_html_content .= '<br>'.'<i>'.$jdata["weather"]["12h"].'</i><br>';
        }
        return $my_html_content;
    }
    
    /** generuj html prognoz wg odebranych danych i opcji
     * 
     * @param type $jdata
     * @param type $instance
     * @return string
     */
    private function  html_weather_forecast_if_need($jdata, $instance) {
        
        $my_html_content = '';
        if(isset($jdata["forecast"]) && $instance['forecast']) {
            $mydata = array();
            foreach ($jdata["forecast"] as $key => $row1) {
                if (strpos($a, 'audio') === false) {
                    $mydata[$key] = array('text'=> $row1,
                                          'audio_url' => NULL);
                    if(isset($jdata["forecast"][$key.'_audio'])) {
                        $mydata[$key]['audio_url'] = $jdata["forecast"][$key.'_audio'];
                    }
                }
            }
            if($mydata) {
                $key_ymd_list = $this->produce_next4_key_ymd_list();
                $lp_days = $instance['forecast']; // na ile dni ta prognoza
                foreach ($key_ymd_list as $row2) {
                    $key = $row2['0'];
                    $head = $key;
                    if($row2['1']) {
                        $head = $row2['1'];
                    }
                    if (isset($mydata[$key]) && $lp_days > '0') {
                        $my_head = '<u>'.$head.':</u>';
                        if(isset($mydata[$key]["audio_url"]) && $instance['audio']) {
                            $my_html_content .= 
                                    $this->html_img_speaker($instance)
                                    .' '
                                    .'<a href="'.$mydata[$key]["audio_url"].'">'.$my_head
                                    .'</a>';
                        }else{
                            $my_html_content .= $my_head;
                        }
                        $my_html_content .= '<br><i>'.$mydata[$key]['text'].'</i><br>';
                        $lp_days--;
                    }
                }
            }
        }
        return $my_html_content;
    }
    
    /** podpisz mną
     * 
     * @param type $jdata
     * @return string
     */
    private function html_powered_by($jdata) {
        
        $my_html_content .= '<p style="text-align:right;">'
                . 'Powered by '
                . '<a href="http://www.wiks.eu">'
                . '&copy;2018 WikS.eu'
                . '</a>'
                . '</p>';
        return $my_html_content;
    }
    
    /** Wyświetlanie widgetu uzytkownikowi
     * 
     * @param type $args
     * @param type $instance
     */
    public function widget( $args, $instance) {
        
        add_action( 'wp_print_footer_scripts', 'addFrontendJavascript' ); 
        
        extract($args);
//        echo $before_widget;
        
        /*if(!empty($instance['tekst'])) {
            echo '<p>' . $instance['tekst'] . '</p>';
        }*/
        
        $url = 'http://api.wiks.eu/weather';
        $result = file_get_contents($url); // pytam i odbieram odpowiedź JSON
        $jdata = json_decode($result, true);
        // buduje skrypt JS - dane startowe
        $my_js_settings = '<script>';
        $my_js_settings .= 'var wiks_weather_now ="'.$instance['now'].'";';
        $my_js_settings .= 'var wiks_weather_12h ="'.$instance['12h'].'";';
        
        $my_html_content = '';
        $my_html_content .= $this->html_weather_header($jdata);
        $my_html_content .= $this->html_weather_now_if_need($jdata, $instance);
        $my_html_content .= $this->html_weather_12h_if_need($jdata, $instance);
        $my_html_content .= $this->html_weather_forecast_if_need($jdata, $instance);
        $my_html_content .= $this->html_powered_by($jdata);
        
        $my_js_settings .= '</script>';
        echo '<div class="widget" id="wiks_weather_content">'.$my_html_content.'</div>';
        echo $my_js_settings;
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


{
  "dt": "20180506 203000", 
  "forecast": {
    "20180507": "bezchmurne niebo, temp. +10...+14\u00b0C, powiew...\u0142agodny wiatr (1-3B) z kierunk\u00f3w NE, E, ci\u015bnienie 1036...1040 hPa, zachmurzy si\u0119 do 0/8 , wilgotno\u015b\u0107 76...93 %", 
    "20180507_audio": "http://www.wiks.eu/weather/audio/20180506181615_1ITC60W.wav", 
    "20180508": "bezchmurne niebo, temp. +11...+16\u00b0C, s\u0142aby...umiarkowany wiatr (2-4B) z kierunk\u00f3w NE, SE, ci\u015bnienie 1028...1035 hPa, zachmurzy si\u0119 do 0/8 , wilgotno\u015b\u0107 69...85 %", 
    "20180508_audio": "http://www.wiks.eu/weather/audio/20180506181618_2EFRYDD.wav", 
    "20180509": "w godz....-10 i 14-... bezchmurne niebo, temp. +12...+18\u00b0C, \u0142agodny...umiarkowany wiatr (3-4B) z kierunku E, ci\u015bnienie 1022...1027 hPa, zachmurzenie 0-1/8, wilgotno\u015b\u0107 66...78 %", 
    "20180509_audio": "http://www.wiks.eu/weather/audio/20180506181623_3ZHZROA.wav"
  }, 
  "place": "Poland, \u015awinouj\u015bcie", 
  "weather": {
    "12h": "bezchmurne niebo, temp. +10...+14\u00b0C, s\u0142aby wiatr ( 2B ), ci\u015bnienie 1040 hPa, zachmurzenie 0/8, wilgotno\u015b\u0107 85...93 %", 
    "12h_audio": "http://www.wiks.eu/weather/audio/20180506204817_fHGU6JX.wav", 
    "now": "bezchmurne niebo, temperatura +17\u00b0C, wiatr z kierunku N 2B (ok.3m/s), ci\u015bnienie 1026hPa, zachmurzenie 0/8 (0%), wilgotno\u015b\u0107 48% ", 
    "now_audio": "http://www.wiks.eu/weather/audio/20180506204803_wA0B5JB.wav", 
    "now_effect_id": 800, 
    "now_icon_url": "http://www.wiks.eu/weather/img/icos/3.gif"
  }
}

 */