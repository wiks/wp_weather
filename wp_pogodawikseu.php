<?php

/**
Plugin Name: PogodaWiksEu
Version: 1.0
Description: Wtyczka 2.0 wyświetlająca pogodę na stronie
Author: WikS.eu
Author URI: http://wiks.eu
Plugin URI: http://pogoda.wiks.eu/about/index.php/wp-widget/
 */

add_action( 'widgets_init', 'wiks_register_pogodawikseu_widget' );


function wiks_register_pogodawikseu_widget() {
    
    register_widget( 'PogodaWiksEu_Widget');
}

/** klasa pobrania i wstępnego sprawdzenia danych z pogoda.wiks.eu
 * 
 */
class GETpogodaWiksEu {
    
    
    /** przygotuj odebrany JSON i sprawdź na array do wyświetlenia
     * 
     * @param type $value
     * @return type
     */
    private function prepare_one_forecast($value) {
        
        $tmp_key_fordate = $value->for_date;
        $tmp_one_day_forecast = array(
//                            'for_date'=> $value->for_date,
            'descr'=> $value->forec_descr,
            'url'=> NULL,
        );
        if(!empty($value->url)){
            $tmp_one_day_forecast['url'] = $value->url;
        }
        return array($tmp_key_fordate, $tmp_one_day_forecast); 
    }
    
    /** przetwórz dane dla pogody i prognozy
     * 
     * @param type $weather_object
     * @return type
     */
    private function process_w($weather_object) {
        
        $vcontent = array('type'=> 'w',
            'name'=> $weather_object->w['0']->name,
            'w'=> array(
                'dt'=> $weather_object->w['0']->dt,
                'descr'=> $weather_object->w['0']->descr_now,
                'descr_12h'=> $weather_object->w['0']->descr_forecast,
                'img'=> $weather_object->w['0']->img_forecast,
                'url'=> $weather_object->w['0']->urln,
                'url_12h'=> $weather_object->w['0']->urlf,
            ),
            'f'=> NULL,
            'dt'=> NULL,
        );
        if(!empty($weather_object->f)) {
            $this_date = NULL;
            foreach($weather_object->f as $value) {
                $this_date = date('Y-M-d H:i:s', strtotime($value->dt)); // 
                list($tmp_key_fordate, 
                        $tmp_one_day_forecast) = $this->prepare_one_forecast($value);
                if(!$vcontent['f']){
                    $vcontent['f'] = array();
                }
                $vcontent['f'][$tmp_key_fordate] = $tmp_one_day_forecast;
            }
            if($this_date) {
                $vcontent['dt'] = $this_date;
            }
        }                
        return $vcontent;
    }
    
    
    /** sprawdź i przetwórz dane gdy tylko powiatowa pogoda
     * 
     * @param type $weather_object
     * @return type
     */
    private function process_p($weather_object) {
        
        $vcontent = array('type'=> 'p',
            'name'=> $weather_object->p->name,
            'f'=> NULL,
            'dt'=> NULL,
            );
        $vars = get_object_vars( $weather_object->p );
        $this_date = NULL;
        foreach($vars as $key=>$value) {
            if( !empty($value->for_date) && !empty($value->forec_descr) && !empty($value->dt) ) {
                // TODO sprawdzanie przeszłych dat?
                // jedna dla momentu powstania prognozy
                $this_date = date('Y-M-d H:i:s', strtotime($value->dt)); // OK
//                        $this_fordate = date('Y-M-d', strtotime($value->for_date)); // OK
                list($tmp_key_fordate, 
                        $tmp_one_day_forecast) = $this->prepare_one_forecast($value);
            }
            if(!$vcontent['f']){
                $vcontent['f'] = array();
            }
            $vcontent['f'][$tmp_key_fordate] = $tmp_one_day_forecast;
            if($this_date) {
                $vcontent['dt'] = $this_date;
            }
        }
        return $vcontent;
    }
    
    /** dla danego tokena pobierz i sprawdź
     * 
     * @param type $token
     */
    public function get_and_validate_for_token($token) {

        $vcontent = NULL;
        $url = 'http://pogoda.wiks.eu/api.php';
        $data = array('t' => $token);
        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === FALSE) { /* Handle error */ 
            
        }else{
            $weather_object = json_decode($result);
            if(!empty($weather_object->w)) {
                $vcontent = $this->process_w($weather_object);
            }
            if(!empty($weather_object->p)) {
                $vcontent = $this->process_p($weather_object);
            }
        }
        return $vcontent;
    }
     
}

/** klasa widgetu
 * 
 */
class PogodaWiksEu_Widget extends WP_Widget {
    
    /**
     * Sets up the widgets name etc
     */
    public function __construct() {

        $widget_id = 'pogodawikseu';
	$widget_name = __('Pogoda.Wiks.Eu', 'PogodaWiksEu_Widget');
	$widget_opt = array('description'=>'Ten widget wyświetla pogodę i/lub prognozę pogody.');

	parent::__construct($widget_id, $widget_name, $widget_opt);        
    }
       
    /** zamień index dnia tygodnia na nazwę dnia tygodnia
     * 
     * @param type $dwi
     * @return string
     */
    private function wd($dwi=NULL) {
    
        if($dwi === NULL) {
            $dwi = date( "w", time());
        }
        $wd_arr = array(
            'niedziela', 'poniedziałek',  'wtorek',  'środa',  'czwartek',  'piątek',  'sobota'
        );
        return $wd_arr[ $dwi % '7' ];
    }
    
    /** uładnij datę
     * 
     * @param type $key
     * @param type $dt_d
     * @param type $dt_j
     * @return type
     */
    private function prittyfy_dt($key) {
     
        $dt_d = date('Y-m-d', time());
        $dt_j = date('Y-m-d', strtotime('+1 day', time()));
        
        $ifpretty_key = '';
        if($key == $dt_d) {
            $ifpretty_key = 'dziś';
        }
        else if($key == $dt_j) {
            $ifpretty_key = 'jutro';
        }
        else{
            $dt_weekday = $this->wd(date('w', strtotime($key)));
            $ifpretty_key = $dt_weekday;
        }
        return $ifpretty_key;
    }    
    
    /** Wyświetlanie widgetu uzytkownikowi
     * 
     * @param type $args
     * @param type $instance
     */
    public function widget($args, $instance) {
        
        $my_html_content = '';
        $g = new GETpogodaWiksEu();
        $vcontent = $g->get_and_validate_for_token($instance['token']);
        if($vcontent) {
            if(!empty($vcontent['name']) && !empty($vcontent['f'])) {
                $my_html_content = '<h3>'.$vcontent['name'].'</h3>';
                $lp_days = '0';
                foreach($vcontent['f'] as $key=>$value) {
                    if($lp_days++ < $instance['max_days']) {
                        $my_html_content .= '<h4>';
                        
                        if(!$instance['days_as_date_only'] === true) {
                            $inside_key = $key;
                        }else{
                            $inside_key = $this->prittyfy_dt($key);
                        }
                        if($instance['allow_audio'] === true && !empty($value['url'])){
                            $my_html_content .= '<a href="'.$value['url'].'">'.$inside_key.'</a>';
                        }else{
                            $my_html_content .= $inside_key;
                        }
                        $my_html_content .= ':</h4>';
                        $my_html_content .= $value['descr'].'<br>';
                    }
                }
            }
        }
        echo '<div class="widget" id="wiks_weather_content">'.$my_html_content.'</div>';
    }
    
    /**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     */
    public function form( $instance ) {

        //ustawiamy opcje domyslne
        $my_defaults = array(
            'token' => 'for_test_cntcXBWYJDQ0H6VwanN3Zrh', // test token
            'max_days' => 3,
            'days_as_date_only' => false,
            'allow_audio' => true,
        );
        $instance = wp_parse_args( (array) $instance, $my_defaults );
    ?>
        <p>
            <!-- token -->
            <label for="<?php echo $this->get_field_id('token'); ?>"><?php _e('Token:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('token'); ?>" name="<?php echo $this->get_field_name('token'); ?>" type="text" value="<?php echo esc_attr($instance['token']); ?>" />
        </p>
        <p>
            <!-- max days show -->
            <label for="<?php echo $this->get_field_id( 'max_days' ); ?>"><?php _e( 'Maksymalna liczba dni prognozy:' ); ?></label>
            <input id="<?php echo $this->get_field_id( 'max_days' ); ?>" name="<?php echo $this->get_field_name( 'max_days' ); ?>" type="text" value="<?php echo $instance['max_days']; ?>" size="3" />
        </p>
        <p>
            <!-- czy zmieniać daty na przyjazne opisy dziś jutro -->
            <input class="checkbox" type="checkbox" name="<?php echo $this->get_field_name('days_as_date_only'); ?>" id="<?php echo $this->get_field_id('days_as_date_only'); ?>" value="true" <?php checked(true, $instance['days_as_date_only']);?> />
            <label for="<?php echo $this->get_field_id('days_as_date_only'); ?>"> <?php _e( 'pokaż przyjazne opisy dni np. "dziś" zamiast "'.date('Y-m-d', time()).'"' ); ?></label><br />
        </p>
        <p>
            <!-- czy tworzyć linki do audio -->
            <input class="checkbox" type="checkbox" name="<?php echo $this->get_field_name('allow_audio'); ?>" id="<?php echo $this->get_field_id('allow_audio'); ?>" value="true" <?php checked(true, $instance['allow_audio']);?> />
            <label for="<?php echo $this->get_field_id('allow_audio'); ?>"> <?php _e( 'utwórz odnośniki do audio' ); ?></label><br />
        </p>        
    <?php         
    }

    /**
     * Processing widget options on save
     *
     * @param array $new_instance The new options
     * @param array $old_instance The previous options
     */
    public function update( $new_instance, $old_instance ) {
        
        $instance = $old_instance;
        $instance['token'] = strip_tags($new_instance['token']);
        $instance['max_days'] = absint($new_instance['max_days']);
        if($instance['max_days'] < '1') {
            $instance['max_days'] = '1';
        }
        // czy zmieniać daty na przyjazne opisy dziś jutro
        $instance['days_as_date_only'] = isset($new_instance['days_as_date_only']);
        // czy tworzyć linki do audio
        $instance['allow_audio'] = isset($new_instance['allow_audio']);        
        return $instance;
    }    
}
