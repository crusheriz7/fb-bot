<?php defined('BASEPATH') OR exit('No direct script access allowed');

/*
* Author      : DulLah
* Name        : Facebook Bot (fb-bot)
* Version     : 1.0
* Update      : 30 mei 2020
* Facebook    : https://www.facebook.com/dulahz
* Telegram    : https://t.me/unikers
*
* Changing/removing the author's name will not make you a real programmer
* Please respect me for making this tool from the beginning. :)
*/

class App extends CI_Controller {
  /*
    @Base url
  */
  public $base_url = 'https://mbasic.facebook.com';
  /*
    @User agent
    @Ganti dgn user agent perangkat anda kalo bisa
  */
  public $user_agent = 'Chrome/36.0.1985.125';

  public $current_version = '1.0';
  public $next_version = '1.1';
  public $yellow = "\e[93m";
  public $cyan = "\e[96m";
  public $green = "\e[92m";
  public $red = "\e[91m";
  public $reset = "\e[0m";

  public function __construct()
  {
    parent::__construct();
    date_default_timezone_set('Asia/Jakarta');
    $this->climate = $this->configs->climate();
    $this->return_menu = & get_instance();
  }

  public function cek_session()
  {
    $this->cookies = $this->configs->load_cookies();
    if (!$this->cookies)
    {
      $this->auth->login();
      $this->cookies = $this->configs->load_cookies();
      $this->return_menu->menu();
    }
    else
    {
      $this->return_menu->menu();
    }
  }

  private function logout()
  {
    $input = $this->climate->br()->input("  Are you sure? [{$this->yellow}Y/n{$this->reset}]:");
    $input = $input->prompt();
    if (strtolower($input) == 'y')
    {
      unlink('log/cookies.txt');
      $this->climate->br()->out('  Cookies successfully removed');
      sleep(3);
      $this->cek_session();
    }
    else
    {
      $this->climate->br()->out('  Canceled');
      sleep(3);
      $this->cek_session();
    }
  }

  private function _curl_version()
  {
    $response = file_get_contents('https://raw.githubusercontent.com/dz-id/fb-bot/master/version.txt');
    if (trim($response) == $this->current_version)
    {
      return FALSE;
    }
    else if (trim($response) == $this->next_version)
    {
      return TRUE;
    }
    else
    {
      return FALSE;
    }
  }

  private function update()
  {
    if (!$this->_curl_version())
    {
      $this->climate->br()->shout('  Already up to date');
      $this->configs->back_menu();
    }
    else
    {
      $this->climate->br()->info('  Updating...')->br();
      system('git pull origin master');
      exit(0);
    }
  }

  public function index()
  {
    $this->cek_session();
  }

  public function menu()
  {
    $this->configs->clear();
    $date = date('D M j G:i:s Y');
    echo sprintf($this->configs->banner(), $date, 'Checking cookies...');
    $version = $this->_curl_version();
    $response = $this->configs->request_get($this->base_url.'/profile.php', $this->cookies);
    if (strpos($response, 'mbasic_logout_button') === FALSE)
    {
      $this->configs->clear();
      $date = date('D M j G:i:s Y');
      echo sprintf($this->configs->banner(), $date, 'Invalid cookies');
      unlink('log/cookies.txt');
      $this->climate->shout('  [WARNING] Cookies invalid please login again.');
      $input = $this->climate->br()->input('  Press enter');
      $input->prompt();
      $this->auth->login();
      return false;
    }
    $dom = new DOMDocument();
    @$dom->loadHTML($response);
    $name = $dom->getElementsByTagName('title');
    $name = $name->item(0)->nodeValue;
    $this->configs->clear();
    $date = date('D M j G:i:s Y');
    echo sprintf($this->configs->banner(), $date, $name);
    if ($version)
    {
      $this->climate->info("     New version is available. update your fb-bot now, type 'ut' for update");
    }
    $this->climate->out('  +------------------------------------------------------+');
    $this->climate->out("    [{$this->yellow}RC{$this->reset}]. Remove cookies");
    $this->climate->out("    [{$this->yellow}RB{$this->reset}]. Report Bug");
    $this->climate->out("    [{$this->yellow}UT{$this->reset}]. Update Tools");
    $this->climate->out("    [{$this->yellow}EX{$this->reset}]. Exit");
    $this->climate->out('  +------------------------------------------------------+');
    foreach ($this->configs->show_menu() as $menu)
    {
      $this->climate->out("    [{$this->yellow}{$menu->no}{$this->reset}]. $menu->name");
    }
    $input = $this->climate->br()->input("  Choice:");
    $input = $input->prompt();
    $title = $this->configs->show_menu()[$input-1]->name;
    switch (strtolower($input))
    {
      case 'rc':
        $this->logout();
        break;
      case 'rb':
        $this->tools->report_bug('Report Bug');
        break;
      case 'ut':
        $this->update();
        break;
      case 'ex':
        $this->climate->br()->yellow("  Thank you don't forget to come back again :)");
        exit(0);
      case '01':
        $this->tools->chat_messages_eraser($title);
        break;
      case '02':
        $this->tools->post_eraser($title);
        break;
      case '03':
        $this->tools->friendslist_eraser($title);
        break;
      case '04':
        $this->tools->photo_album_eraser($title);
        break;
      case '05':
        $this->tools->friends_request($title);
        break;
      case '06':
        $this->tools->mass_join_groups($title);
        break;
      case '07':
        $this->tools->update_status($title);
        break;
      case '08':
        $this->tools->mass_chat($title);
        break;
      case '09':
        $this->tools->spam_chat($title);
        break;
      case '10':
        $this->tools->mass_leave_group($title);
        break;
      case '11':
        $this->tools->mass_react($title);
        break;
      case '12':
        $this->tools->mass_comments($title);
        break;
      default:
        $this->climate->br()->shout('  Wrong Input');
        sleep(3);
        $this->index();
        break;
    }
  }
}