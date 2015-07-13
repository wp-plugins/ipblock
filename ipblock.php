<?php


defined('ABSPATH') or die();

class IPBlock {

	public function __construct() {
		$this->schedule_frequency=300; #default schedule frequency, overriden if option exists
		add_filter('cron_schedules', array($this,'set_schedule_frequency'));
		add_action('ipblock_records_cleanup',array($this,'records_cleanup') );
		if (get_option($this->option_name)) {
			global $wpdb;
			$this->settings=get_option($this->option_name);
			$this->dbtab=$dbtab=$wpdb->prefix.'ipblock';
			$this->scheme=$this->settings['scheme'];
			$this->schedule_frequency=$this->settings['schedule_frequency'];
			$this->delay_time=0;
			$this->output=0;
			add_filter('authenticate',array($this, 'main'),30,3);
		}
   }
	public function main($user,$username ='',$password='') {
		$this->get_data();
		#check if login credentials are submitted
	 	if (!empty($username) and !empty($password)) $is_login_attempt=1;
		else $is_login_attempt=0;
		$this->is_login_attempt=$is_login_attempt;

		add_action('login_form',array($this,'login_footer'));

		$settings=$this->settings;
		$time_remaining=$this->unblock_time-time(); #negative unix time, if block rule doesn't exist

		if ($time_remaining>0) { # return with ipblock message

			$this->format_time($time_remaining);
			if ($settings['mode']==1) $message=sprintf($settings['mode1_message_header'],$time_remaining);
			elseif ($settings['mode']==2) $message=sprintf($settings['mode2_message_header'],$time_remaining);
			$this->output=1;
			return new WP_Error('ipblock',$message);
		}
		else {
			if ($is_login_attempt) $this->log_ip();
			return $user; //pass through this filter
		}

	}

	public function login_footer() {
		$settings=$this->settings;
		$attempts=$this->attempts;

		$delay=$this->delay_time; #0 if unblock time wasnt updated recently
		$this->format_time($delay);
		#display footer message that delay was set after setting a delay
		if($settings['mode']==1 and $this->delay_time>0) {
			$this->output=1;
			printf("<p>$settings[mode1_message_footer]</p>",$delay);
			echo '<br />';
		}
		#display number of attempts used
		elseif ($settings['mode']==2 and $attempts>0) {
			$this->output=1;
			if ($this->unblock_time<time() ) printf("<p>$settings[mode2_message_footer1]</p>",$attempts,$settings['mode2_attempts']);
			elseif($this->delay_time>0) printf("<p>$settings[mode2_message_footer2]</p>",$delay);
			echo '<br />';
		}
		#display ipblock credits
		if ($settings['credits']==2 or ($settings['credits']==1 and ($this->output)))
			echo '<p>Login protection by <a href="'.$this->plugin_url.'">IPBlock</a></p><br />';

	}

	private function get_data() { #get data from database
		global $wpdb;
		$TIME=time();
		$ip=$_SERVER['REMOTE_ADDR'];
		$dbtab=$this->dbtab;
 
		$result=$wpdb->get_results("SELECT attempts,unblock_time,exp_time FROM $dbtab WHERE ip='$ip' AND exp_time>$TIME", ARRAY_A);

		if (empty($result)) { $this->unblock_time=0; $this->attempts=0; $this->exp_time=0; }
		else { 
			$result=$result[0]; //select only first result if more than one
			$this->unblock_time=$result['unblock_time']; 
			$this->attempts=$result['attempts'];
			$this->exp_time=$result['exp_time'];			
		}
	}

	private function log_ip() {
		global $wpdb;
		$TIME=time();
		
		$ip=$_SERVER['REMOTE_ADDR'];
		$dbtab=$this->dbtab;
		$this->attempts++;
		$attempts=$this->attempts;
		$settings=$this->settings;

		if ($settings['mode']==2) { 
			#in mode 2 expiration time is the restriction, unblock time must be equal to expiration time
			if ($attempts===1) $this->exp_time=$settings['mode2_time']+$TIME;
			if ($attempts==$settings['mode2_attempts']) { 
				$this->unblock_time=$unblock_time=$this->exp_time;
				$this->delay_time=$this->exp_time-time();
			}
			else $unblock_time=$TIME;
		}

		else { #mode1
			#expiration time is refreshed on every attempt
			$this->exp_time=$settings['exp_time']+time();
			#generate unblock time based on number of attempts and scheme for mode 1
			$scheme=$this->scheme;
			$scheme[0]=0;
			ksort($scheme);
			$key=array_keys($scheme);
			$size=sizeof($scheme);
			for ($l=0; $l<$size; $l++) if ($attempts>=$key[$l]) $unblock_time=$scheme[$key[$l]];
			$this->delay_time=$unblock_time;
			$unblock_time+=time();
			$this->unblock_time=$unblock_time;
		}

		$exp_time=$this->exp_time;

		if ($attempts===1) {
		$wpdb->query("INSERT INTO $dbtab (ip, attempts, unblock_time, exp_time) VALUES ('$ip', $attempts, $unblock_time, $exp_time)");
		} 
		else
		$wpdb->query("UPDATE $dbtab SET unblock_time=$unblock_time, attempts=$attempts, exp_time=$exp_time WHERE ip='$ip' AND exp_time > $TIME");
	}

	private function format_time(&$time) {
		if ($time>0) {
			if ($time<60) $time="$time second";
			elseif ($time>=60 and $time<3600) $time=round($time/60).' minute';
			elseif ($time>=3600) $time=round($time/3600).' hour';
			#add s if needed
			if (intval($time)!=1) $time.='s';
		}
	}

	public function set_schedule_frequency($schedules) {
		// interval in seconds
		$schedules['ipblock_schedule_frequency'] = array('interval' => $this->schedule_frequency, 'display' => 'Every '.$this->schedule_frequency.' seconds');
		return $schedules;
}

	public function records_cleanup() { #scheduled event
		global $wpdb;
		$TIME=time();
		$dbtab=$this->dbtab;
		$wpdb->query("DELETE FROM $dbtab WHERE exp_time<$TIME");
	}

	private $plugin_url='https://wordpress.org/plugins/ipblock';
	private $option_name='ipblock-settings';
	private $dbtab;
	private $settings;
	private $unblock_time; //unix time in which block expires
	private $attempts; //number of active attempts
	private $scheme;
	private $exp_time;
	private $delay_time;
}


#initialize IPBlock object
$ipblock= new IPBlock();
?>
