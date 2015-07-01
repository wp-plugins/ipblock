<?php

defined('ABSPATH') or die();

class IPBlock_Settings {

	public function __construct() {
		
		global $wpdb;
		$this->dbtab=$wpdb->prefix.'ipblock';
		
		$this->plugin_url=plugins_url().'/'.basename(dirname(__FILE__));
		$this->plugin_path=dirname(__FILE__);


		#install/uninstall hooks
		if (!get_option($this->option_name)) register_activation_hook( 'ipblock/index.php', array($this,'install'));

        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'add_settings' ) );
        
        if (basename($_SERVER['PHP_SELF'])==='options-general.php' && isset($_GET['page']) && $_GET['page']===$this->page_id)
			add_action('admin_enqueue_scripts',array($this,'enqueue_settings_scripts'));
        
		#get current values for options
		$this->options=get_option($this->option_name);
    }

	public function install() {
		#default option values
		$this->options=array('mode'=>1, 'credits'=>1, 'schedule_frequency'=>600, 'exp_time'=>1000, 'mode2_attempts'=>5, 'mode2_time'=>300);
		$this->options['scheme']=array(2=>5, 3=>15, 5=>30, 10=>45);
		$this->options['scheme_input']='2=>5; 3=>15; 5=>30; 10=>45;';
		$this->options['mode1_message_header']='IPBlock: You have to wait %s to log in again.';
		$this->options['mode1_message_footer']='Delay was set. You have to wait %s to log in again.';
		$this->options['mode2_message_header']='IPBlock: You have run out of attempts. You have to wait %s to log in again.';
		$this->options['mode2_message_footer1']='You have used %d out of %d login attempts.';
		$this->options['mode2_message_footer2']='You have run out of attempts. You can log in again after %s.';
		update_option($this->option_name, $this->options);		
		#create records table
		global $wpdb;
		$wpdb->query("CREATE TABLE ".$this->dbtab." (ip VARCHAR(255) NOT NULL,attempts INT UNSIGNED, 
		unblock_time INT UNSIGNED NOT NULL, exp_time INT UNSIGNED NOT NULL)");
		#schedule cleanup event
		wp_schedule_event(time(),'ipblock_schedule_frequency','ipblock_records_cleanup');
	}


	public function records_purge() {
		global $wpdb;
		$dbtab=$this->dbtab;
		$wpdb->query("DELETE FROM $dbtab");
	}
	
	public function enqueue_settings_scripts() {
		wp_enqueue_script('ipblock_settings', $this->plugin_url.'/includes/js/settings.js', array('jquery'));
		wp_enqueue_style('ipblock_settings', $this->plugin_url.'/includes/css/settings.css');
		
		$icon_close=file_get_contents($this->plugin_path.'/includes/assets/icons/close.svg');
		wp_localize_script( 'ipblock_settings', 'PHP',array('icon_close' => $icon_close));
		

	}

	public function add_plugin_page() {
		add_options_page( 'IPBlock Settings', 'IPBlock', 'manage_options', $this->page_id, array($this,'plugin_page') );
	}

	public function plugin_page() {
		
		?>
		
		<div class="wrap">
			
			
		<h2>IPBlock Settings</h2>

		
			
		<div id="ipblock-settings-about">
						
			
			<div id="ipblock-settings-windowpress">
			
			
			
			<div class="about-section">
				<p>Check out my new plugin WindowPress. It allows you to open multiple pages in the administration area, so you can do many admin tasks in just one browser tab.</p>
				<a href="https://wordpress.org/plugins/windowpress/" target="_blank"><img src="<?php echo $this->plugin_url.'/includes/assets/images/windowpress.png'; ?>" /></a>
			</div>
			
			
			</div>
			
	
			<div class="about-row-2">

				<div class="about-section">
				<p>If you like this plugin and find it useful, please consider donating.</p>
				<a class="button button-blue mobile-button-noborder-right" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=P5UC9VG3Q687N" target="_blank">Donate</a>
				</div>
			
				<div class="about-section">
				<p>Your feedback is important.</p>
				<a class="button button-red mobile-button-noborder-left mobile-button-noborder-right" href="https://wordpress.org/support/view/plugin-reviews/ipblock?rate=5#postform" target="_blank">Review</a>
				</div>
			
				<div class="about-section">
				<p>Need help, found a bug? Check the Support Forum and Frequently Asked Questions.</p>
				<a class="button button-green button-half button-left mobile-button-noborder-left" href="https://wordpress.org/support/plugin/ipblock" target="_blank">Forum</a>
				<a class="button button-orange button-half button-right" href="https://wordpress.org/plugins/ipblock/faq/" target="_blank">FAQ</a>
				</div>
			
			</div>
			
		</div>
		
		
		<div id="ipblock-settings-options">
			
			<p>Note that everytime you change configuration all ip records are cleared</p>

			<form method="post" action="options.php"><?php
			settings_fields($this->option_group);
			do_settings_sections($this->page_id);
			submit_button();
			?></form>
		</div>
		
		
		<?php
				
		echo '</div>';//wpwrap
		
		
		
		
	}
	
	
	public function add_settings() {
		register_setting( $this->option_group, $this->option_name,array( $this, 'sanitize'));
		#general settings
		add_settings_section($this->section_general,'General Settings',array( $this, 'section_general' ),$this->page_id);
		add_settings_field('mode','Select mode',array( $this, 'mode_callback' ),$this->page_id,$this->section_general);
		add_settings_field('credits','Display credits',array( $this, 'credits_callback' ),$this->page_id,$this->section_general,
			array('decide wether to display IPBlock credits message ("Login protection by IPBlock"). Credits will be displayed under the login form. If you check "on IPBlock output" credits will be displayed only when IPBlock sends output to user (error message or notice).'));
		add_settings_field('schedule_frequency','Cleanup expired records every ',array( $this, 'text_callback' ),$this->page_id,$this->section_general,
			array('schedule_frequency',''));
		#mode1 settings
		add_settings_section($this->section_mode1,'Mode 1',array($this,'section_mode1'),$this->page_id);
		add_settings_field('scheme_input','Delay scheme',array($this,'scheme_callback'),$this->page_id,$this->section_mode1,
			array('scheme_input','Check FAQ for details.'));
		add_settings_field('exp_time','Record expiration time',array( $this,'text_callback'),$this->page_id,$this->section_mode1,
			array('exp_time','Check FAQ for details'));
		add_settings_field('mode1_message_header','IPBlock error',array($this,'text_callback'),$this->page_id,$this->section_mode1,
			array('mode1_message_header','This is a message that is displayed when someone tries to log in or just visits login page while being blocked.<br />%s is time remaining.'));
		add_settings_field('mode1_message_footer','Delay set notice',array($this,'text_callback'),$this->page_id,$this->section_mode1,
			array('mode1_message_footer','This is a notice that block was set. It displays right after failed login attempt (if block was set).<br />%s is time remaining.'));
		#mode2 settings
		add_settings_section($this->section_mode2,'Mode 2',array( $this, 'section_mode2' ),$this->page_id);
		add_settings_field('mode2_attempts','Attempts allowed',array($this,'text_callback'),$this->page_id,$this->section_mode2,
			array('mode2_attempts',''));
		add_settings_field('mode2_time','Time Period',array( $this, 'text_callback' ),$this->page_id,$this->section_mode2,
			array('mode2_time',''));
		add_settings_field('mode2_message_header','IPBlock error',array($this,'text_callback'),$this->page_id,$this->section_mode2,
			array('mode2_message_header','This is a message that is displayed when someone tries to log in or just visits login page while being blocked.<br />%s is time remaining.'));
		add_settings_field('mode2_message_footer1','Attempts used notice',array($this,'text_callback'),$this->page_id,$this->section_mode2,
			array('mode2_message_footer1','This is a notice displayed under login form, informing how many attempts out of available were used. Isn\'t displayed if no attempt was made or number of attempts reached its limit.<br />First %d stands for attempts used and second %d is total attempts allowed.'));
		add_settings_field('mode2_message_footer2','Block set notice',array($this,'text_callback'),$this->page_id,$this->section_mode2,
			array('mode2_message_footer2','This is a notice displayed under login form, right after last allowed attempt was made. It informs the user that he reached the maximum number of attempts and should wait to try again.<br />%s is time remaining.'));
	}
	
	
	#SECTION DESCRIPTIONS
	public function section_general() { echo '<p>General Settings</p>'; }
    public function section_mode1() { echo '<p>Set a custom delay after each login attempt.</p>'; }
	public function section_mode2() { echo '<p>Allow a number of attempts in given time.</p>'; }

	#FORM CALLBACKS
	public function text_callback($args) { $option_id=$args[0]; $description=$args[1];
		if (isset($this->options[$option_id])) $value=esc_attr($this->options[$option_id]);
		else $value=null;
		if ($option_id=='exp_time' or $option_id=='mode2_time' or $option_id=='mode2_attempts' or $option_id=='schedule_frequency') $width='100px';
		else $width='500px';
		echo "<input type=\"text\" id=\"$option_id\" name=\"".$this->option_name."[$option_id]\" style=\"width:$width;\" value=\"$value\" />";
		if ($option_id=='exp_time' or $option_id=='mode2_time' or $option_id=='schedule_frequency') echo ' seconds';
		#Description
		if(!empty($description)) { 
		//		echo '</td></tr><tr>';
		//		echo "<td style='padding:0px; margin-top:-10px; font-size:12px;' colspan='2'>";
				echo "<p class='description'>$description</p>";
		}
	}
	
	public function scheme_callback($args) { $option_id=$args[0]; $description=$args[1];
		if (isset($this->options[$option_id])) $value=esc_attr($this->options[$option_id]);
		else $value=null;
		
		$width='500px';
		echo "<input type=\"text\" id=\"$option_id\" name=\"".$this->option_name."[$option_id]\" style=\"width:$width;\" value=\"$value\" />";
		#Description
		if(!empty($description)) echo "<p class='scheme-description'>$description</p>";
		
		echo '</td></tr><tr>';
		echo "<td style='padding:0px; margin-top:-10px; font-size:12px;' colspan='2'>";
		
		echo '<div id="scheme_wrapper"></div>';

	}

    public function mode_callback() {
		$option_id='mode';
		if (isset($this->options[$option_id])) $value=esc_attr($this->options[$option_id]);
		if ($value=='1') $checked1='checked="checked"'; else $checked1=null;
		if ($value=='2') $checked2='checked="checked"';	else $checked2=null;
		echo "<input type=\"radio\"  name=\"".$this->option_name."[$option_id]\" value=\"1\" $checked1>Mode 1&nbsp;&nbsp;&nbsp;";
		echo " <input type=\"radio\"  name=\"".$this->option_name."[$option_id]\" value=\"2\" $checked2>Mode 2";
    }

	public function credits_callback($args) {
		$option_id='credits';
		$description=$args[0];
		if (isset($this->options[$option_id])) $value=esc_attr($this->options[$option_id]);
		if ($value=='0') $checked0='checked="checked"'; else $checked0=null;
		if ($value=='1') $checked1='checked="checked"'; else $checked1=null;
		if ($value=='2') $checked2='checked="checked"'; else $checked2=null;
		echo "<input type=\"radio\"  name=\"".$this->option_name."[$option_id]\" value=\"0\" $checked0>Never&nbsp;&nbsp;&nbsp;";
		echo " <input type=\"radio\"  name=\"".$this->option_name."[$option_id]\" value=\"1\" $checked1>On IPBlock output&nbsp;&nbsp;&nbsp;";
		echo " <input type=\"radio\"  name=\"".$this->option_name."[$option_id]\" value=\"2\" $checked2>Always&nbsp;&nbsp;&nbsp;";
		#Description
		if(!empty($description)) { 
		//		echo '</td></tr><tr>';
		//		echo "<td style='padding:0px; margin-top:-10px; font-size:12px;' colspan='2'>";
				echo "<p class='description'>$description</p>";
		}
	}

	#SANITIZE
    public function sanitize($input) {
		$new_input = array();
		$limit=$this->input_integer_limit;

		if (isset($input['mode'])) {
			if ($input['mode']==1 or $input['mode']==2) $new_input['mode']=intval($input['mode']);
			else $new_input['mode']=1;
		}
		if (isset($input['credits'])) {
			if ($input['credits']==0 or $input['credits']==1 or $input['credits']==2) $new_input['credits']=intval($input['credits']);
			else $new_input['credits']=1;
			
		}
		#sanitize text inputs
		$text_inputs=array(0=>'mode1_message_header',1=>'mode1_message_footer',2=>'mode2_message_header',
		3=>'mode2_message_footer1',4=>'mode2_message_footer2');
		foreach ($text_inputs as $text_input) {
			if(isset($input[$text_input])) $new_input[$text_input]=sanitize_text_field($input[$text_input]);
		}
		#validate integer inputs
		$int_inputs=array('schedule_frequency'=>'Incorrect value for Schedule frequency.', 'exp_time'=>'Incorrect value for Expiration time.', 
		'mode2_attempts'=>'Incorrect value for Attempts.', 'mode2_time'=>'Incorrect value for Time period.');
		foreach ($int_inputs as $field=>$message) {
			if (isset($input[$field])) {
				$value=filter_var($input[$field], FILTER_VALIDATE_INT);
				if ($value>0 and $value<$limit) $new_input[$field]=$value;
				else { 
					$new_input[$field]=$this->options[$field];
					add_settings_error($field,esc_attr('settings_error'),$message,'error');
				}
			}
		}
		#validate and sanitize scheme
		if(isset($input['scheme_input'])) {
			#option scheme_input is text version of option scheme that is converted to an array and stored as scheme
			#scheme_input is used only on the option page while scheme is an array used directly by IPBlock object

			$scheme_input=str_replace(' ', '', $input['scheme_input']); //remove spaces
			$scheme_input=trim($scheme_input, ';'); //remove last semicolon
			$pairs=explode(';',$scheme_input);
			$scheme=array();
			$error=0;
			#generate scheme array based on scheme_input, if something goes wrong return with validation error
			foreach ($pairs as $pair) {
				$value=explode('=>',$pair);
				if (sizeof($value)===2) {
					$value[0]=filter_var($value[0], FILTER_VALIDATE_INT); #false if not integer
					$value[1]=filter_var($value[1], FILTER_VALIDATE_INT);
					if ( $value[0]>0 and $value[1]>0 and $value[0]<$limit and $value[1]<$limit )
						$scheme[$value[0]]=$value[1];
					else { $error=1; break; }
				}
				else { $error=1; break; }

			}
			ksort($scheme);

			if($error) {
				$new_input['scheme_input']=$this->options['scheme_input'];
				$new_input['scheme']=$this->options['scheme'];
				$message='Incorrect scheme.';
				add_settings_error('scheme_input',esc_attr('settings_error'),$message,'error');
			}
			else {
				$this->options['scheme']=$scheme;
				$new_input['scheme']=$scheme;
				#generate scheme_input option based on scheme array
				$scheme_input='';
				foreach($scheme as $attempts=>$delay) $scheme_input.="$attempts=>$delay; ";
				$new_input['scheme_input']=$scheme_input;
			}
		}
		#delete all records when configuration changes
		$this->records_purge();
		return $new_input;
		}

	private $options;
	private $dbtab;
	private $input_integer_limit=100000000; //10^8
	private $option_group='ipblock';
	private $page_id='ipblock-settings';
	private $section_general='ipblock-settings-general';
	private $section_mode1='ipblock-settings-mode1';
	private $section_mode2='ipblock-settings-mode2';

	private $option_name='ipblock-settings';

	private $plugin_url;
}

$ipblock_settings = new IPBlock_Settings();

?>
