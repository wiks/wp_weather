<?php

/**
Plugin Name: Moj widgecik
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

class Weather_Widget extends WP_Widget {
    
    function Weather_Widget() {
        // tablica opcji.
        $widget_ops = array(
            'classname' => 'Weather_Widget', //nazwa klasy widgetu
            'description' => 'Pogodowy widżecik', //opis widoczny w panelu
        );
        //ładowanie 
        parent::__construct( 'Weather_Widget', 'Weather wWidget', $widget_ops );
    }    
    
    function form($instance) {
        // Formularz w kokpicie administratora (tam gdzie przeciągamy widgety)
        $defaults = array('tekst' => 'Mój tekst',
                          'now' => false,
                          '12h' => true,
                          'forewind' => true,
            );
        $instance = wp_parse_args( (array) $instance, $defaults);
        $tekst = $instance['tekst'];        
        ?>
        <p>
            <label>Twój tekst</label>
            <input type="text" name="<?= $this->get_field_name('tekst');?>" value="<?= esc_attr($tekst);?>" />
        </p>
        <p>
            <label>Pogoda:</label><br>
            <input type="checkbox" name="<?= $this->get_field_name('now');?>" value="now" <?php echo $instance['now']=='now' ? 'checked="checked"':''; ?> >pogoda teraz<br>
            <input type="checkbox" name="<?= $this->get_field_name('12h');?>" value="12h" <?php echo $instance['12h']=='12h' ? 'checked="checked"':''; ?> >prognoza na 12 godzin<br>
            <input type="checkbox" name="<?= $this->get_field_name('forewind');?>" value="forewind" <?php echo $instance['forewind']=='forewind' ? 'checked="checked"':''; ?> >prognoza na najbliższe dni<br>            
        </p>
        <?php
    }
    
    function update($new_instance, $old_instance) {
        // zapis opcji widgetu
        $instance = $old_instance;
        $instance['tekst'] = strip_tags( $new_instance['tekst']);
        $instance['now'] = strip_tags( $new_instance['now']);
        $instance['12h'] = strip_tags( $new_instance['12h']);
        $instance['forewind'] = strip_tags( $new_instance['forewind']);
        return $instance;        
    }
    
    function widget( $args, $instance) {
        // Wyświetlanie widgetu uzytkownikowi
        
        add_action( 'wp_print_footer_scripts', 'addFrontendJavascript' ); 
        
        extract($args);
        echo $before_widget;
        
        /*if(!empty($instance['tekst'])) {
            echo '<p>' . $instance['tekst'] . '</p>';
        }*/
        
        $url = 'http://api.wiks.eu/weather';
        $result = file_get_contents($url);
        $jdata = json_decode($result, true);
        $my_js_settings = '<script>';
        $my_js_settings .= 'var wiks_weather_now ="'.$instance['now'].'";';
        $my_js_settings .= 'var wiks_weather_12h ="'.$instance['12h'].'";';                    
        if(isset($jdata["weather"])&&($instance['now']=='now'||$instance['12h']=='12h')) {
            $my_html_content = '<strong>Pogoda ('.$jdata["dt"].'):</strong><br>';
            if(isset($jdata["weather"]["now"]) && $instance['now']=='now') {
                $my_html_content .= '<u>teraz:</u>'.'<br>';
                $my_html_content .= $jdata["weather"]["now"].'<br>';
            }
            if(isset($jdata["weather"]["12h"]) && $instance['12h']=='12h') {                
                $my_html_content .= '<u>następne 12h:</u>'.'<br>';
                $my_html_content .= $jdata["weather"]["12h"].'<br>';
            }
        }else{
            $my_html_content = ''; // problem z odebraniem pogody
        }
        $my_js_settings .= '</script>';
        echo '<div id="wiks_weather_content">'.$my_html_content.'</div>';
        echo $my_js_settings;
        echo $after_widget;        
    }
    
    function addFrontendJavascript(){
        
        // wp_register_script('jq-wiks_weather', PLUGIN_PATH . 'js/jquery.hoverintent.js', array(), '1.0', false);
        // https://pippinsplugins.com/using-the-wordpress-heartbeat-api/

    }
}


