<?php
namespace BitmessagePlugin\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use \Html2Text\Html2Text;

/** 
 * BitmessageComponent is a class adapting the PhpBitmessage class that was developed 
 * by Convertor as copyrighted below.
 * 
 * Original class can be found here: 
 * @website http://conver.github.io/class.bitmessage.php/
 */
class BitmessageComponent extends Component {

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'BitMessage' => [
            'rpc_url' => 'http://localhost:8442',
            'rpc_username' => 'username',
            'rpc_password' => 'password',
        ]
    ];
    
    public $components = ['BitmessagePlugin.Curl'];

    private $url;
    private $basefields = array(
        "subject",
        "message",
        "label");
    
    public $errors = "";
    
    private $decode = false;
    private $strip = false;

    public function initialize(array $config = []) {
        parent::initialize($config);

        $this->url = Configure::read('BitMessage.rpc_url');
        $this->connection = $this->Curl->initialize();
        $this->methods = array();
    }
    
    
    
    
    
    
// <editor-fold desc="Public Methods">
    
    public function setStrip($strip) {
        if ($strip) {
            $this->strip = true;
        } else {
            $this->strip = false;
        }
    }

    public function newAddress($label, $eighteenByteRipe = false, $totalDifficulty = 1, $smallMessageDifficulty = 1) {
        $bmdata = array(
            $this->baseencode($label),
            $eighteenByteRipe,
            $totalDifficulty,
            $smallMessageDifficulty);

        return $this->getError($this->call('createRandomAddress', $bmdata));
    }

    public function statusBar($message) {
        $bmdata = array($message);

        $message = $this->getError($this->call('statusBar', $bmdata));
        return ($message) ? true : false;
    }

    public function createDeterministicAddresses($passphrase, $numberOfAddresses, $addressVersionNumber = 0, $streamNumber = 0, $eighteenByteRipe = true, $totalDifficulty = 1, $smallMessageDifficulty = 1) {
        $bmdata = array(
            $this->baseencode($passphrase),
            $numberOfAddresses,
            $addressVersionNumber,
            $streamNumber,
            $eighteenByteRipe,
            $totalDifficulty,
            $smallMessageDifficulty);
        return $this->getError($this->call('createDeterministicAddresses', $bmdata)); // Creating many addresses can take some time & bring up some load!
    }

    public function listAddresses() {
        $addresses = $this->jsondecode($this->call('listAddresses2'));
        return $this->getError($this->cleanArray($addresses->addresses));
    }

    public function getDeterministicAddress($passphrase, $addressVersionNumber = 4, $streamNumber = 1) {
        $bmdata = array(
            $this->baseencode($passphrase),
            $addressVersionNumber,
            $streamNumber);
        return $this->getError($this->call('getDeterministicAddress', $bmdata));
    }

    public function getAllInbox() { // Does not include trashed messages

        $messages = $this->jsondecode($this->call('getAllInboxMessages'));
        return $this->getError($this->cleanArray($messages->inboxMessages));
    }

    public function getInboxMessageByID($msgid, $read = 2) {
        if ($read = 2) {
            $bmdata = array($msgid);
        } else {
            $bmdata = array($msgid, $read);
        }
        $messages = $this->jsondecode($this->call('getInboxMessageByID', $bmdata));
        return $this->getError($this->cleanArray($messages->inboxMessages));
    }

    public function getSentMessageByAckData($ackData) {
        $bmdata = array($ackData);
        $messages = $this->jsondecode($this->call('getSentMessageByAckData', $bmdata));
        return $this->getError($this->cleanArray($messages->sentMessages));
    }

    public function getAllSentMessages() {
        $messages = $this->jsondecode($this->call('getAllSentMessages'));
        return $this->getError($this->cleanArray($messages->sentMessages));
    }

    public function getSentMessageByID($msgid) {
        $bmdata = array($msgid);
        $messages = $this->jsondecode($this->call('getSentMessageByID', $bmdata));
        return $this->getError($this->cleanArray($messages->sentMessages));
    }

    public function getSentMessagesBySender($fromAddress) {
        $bmdata = array($fromAddress);
        $messages = $this->jsondecode($this->call('getSentMessagesBySender', $bmdata));
        return $this->getError($this->cleanArray($messages->sentMessages));
    }

    public function trashMessage($msgid) {
        $bmdata = array($msgid);
        return $this->getError($this->call('trashMessage', $bmdata));
    }

    public function sendMessage($toAddress, $fromAddress, $subject, $message, $encodingType = 2) {
        $bmdata = array(
            $toAddress,
            $fromAddress,
            $this->baseencode($subject),
            $this->baseencode($message),
            $encodingType);
        
        $ret = $this->call('sendMessage', $bmdata);
        print_r($ret);
        return $this->getError($ret);
    }

    public function broadcast($address, $title, $message, $encodingType = 2) {
        $message = $this->stripHTML($message);

        $bmdata = array(
            $address,
            $this->baseencode($title),
            $this->baseencode($message),
            $encodingType);

        return $this->getError($this->call('sendBroadcast', $bmdata));
    }

    public function getStatus($ackData) {
        $bmdata = array($ackData);
        return $this->getError($this->call('sendMessage', $bmdata));
    }

    public function listSubscriptions() {
        $subscriptions = $this->jsondecode($this->call('listSubscriptions'));
        return $this->getError($this->cleanArray($subscriptions->subscriptions));
    }

    public function addSubscription($address, $label = "") {
        if ($label == "") {
            $bmdata = array($address);
        } else {
            $bmdata = array($address, $this->baseencode($label));
        }

        return $this->getError($this->call('addSubscription', $bmdata));
    }

    public function deleteSubscription($address) {
        $bmdata = array($address);
        return $this->getError($this->call('deleteSubscription', $bmdata));
    }

    public function listAddressBookEntries() {
        $AddressBookEntries = $this->jsondecode($this->call('listAddressBookEntries'));
        return $this->getError($this->cleanArray($AddressBookEntries->addresses));
    }

    public function addAddressBookEntry($address, $label) {
        $bmdata = array($address, $this->baseencode($label));
        return $this->getError($this->call('addAddressBookEntry', $bmdata));
    }

    public function deleteAddressBookEntry($address) {
        $bmdata = array($address);
        return $this->getError($this->call('deleteAddressBookEntry', $bmdata));
    }

    public function trashSentMessageByAckData($ackData) {
        $bmdata = array($ackData);
        return $this->getError($this->call('trashSentMessageByAckData', $bmdata)); // in Hex
    }

    public function createChan($passphrase) {
        $bmdata = array($this->baseencode($passphrase));
        return $this->call('createChan', $bmdata);
    }

    public function joinChan($passphrase, $address) {
        $bmdata = array($this->baseencode($passphrase), $address);
        $call = $this->getError($this->call('leaveChjoinChanan', $bmdata));
        if ($call == "success") { // Note that at this time, the address is still shown in the UI until a restart.
            return true;
        } else {
            return false;
        }
    }

    public function leaveChan($address) {
        $bmdata = array($address);
        $call = $this->getError($this->call('leaveChan', $bmdata));
        if ($call == "success") { // Note that at this time, the address is still shown in the UI until a restart.
            return true;
        } else {
            return false;
        }
    }

    public function deleteAddress($address) {
        $bmdata = array($address);
        $call = $this->getError($this->call('deleteAddress', $bmdata));
        if ($call == "success") { // Note that at this time, the address is still shown in the UI until a restart.
            return true;
        } else {
            return false;
        }
    }

    public function decodeAddress($address) {
        $bmdata = array($address);
        $Address = $this->call('decodeAddress', $bmdata);
        return $this->getError($Address);
    }

// </editor-fold>

// <editor-fold desc="Private Methods">

    private function call($method, $params = null) {
        $post = xmlrpc_encode_request($method, $params);
        return xmlrpc_decode($this->Curl->post($this->url, $post));
    }

    private function stripHTML($text) {
        if ($this->strip) {
            return Html2Text::convert($text);
        } else {
            return $text;
        }
    }

    private function getError($check) {
        if (!is_array($check)) {
            if (strpos($check, 'API Error') !== false) {
                $this->errors .= $check;
                return;
            }
        }
        return $check;
    }

    public function autoDecode($decode) {
        if ($decode) {
            $this->decode = true;
        } else {
            $this->decode = false;
        }
    }

    private function baseencode($encode) {
        return base64_encode($encode);
    }

    private function basedecode($decode) {
        return base64_decode($decode);
    }

    private function jsondecode($decode) {
        return json_decode($decode);
    }

    private function cleanArray($array) {
        foreach ($array as $item => $key) {
            $a[$item] = $key;
        }

        if ($this->decode) {
            return $this->basecheck($a);
        }

        return $a;
    }

    private function basecheck($a) {
        foreach ($a as $i => $item) {
            foreach ($item as $parm => $value) {
                if (in_array($parm, $this->basefields)) {
                    $a[$i]->$parm = $this->basedecode($value);
                } else {
                    $a[$i]->$parm = $value;
                }
            }
        }
        return $a;
    }

// </editor-fold>

}
