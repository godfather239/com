<?php
namespace Provider\MerchantStore;
/**
 * Autogenerated by Thrift Compiler (0.9.1)
 *
 * DO NOT EDIT UNLESS YOU ARE SURE THAT YOU KNOW WHAT YOU ARE DOING
 *  @generated
 */
use Thrift\Base\TBase;
use Thrift\Type\TType;
use Thrift\Type\TMessageType;
use Thrift\Exception\TException;
use Thrift\Exception\TProtocolException;
use Thrift\Protocol\TProtocol;
use Thrift\Protocol\TBinaryProtocolAccelerated;
use Thrift\Exception\TApplicationException;


interface MerchantStoreIf {
  public function getUrlByUid($uid);
  public function getStoreLinkByStoreId($ids);
  public function getStoreInfoByStoreIds($ids);
}

class MerchantStoreClient implements \Provider\MerchantStore\MerchantStoreIf {
  protected $input_ = null;
  protected $output_ = null;

  protected $seqid_ = 0;

  public function __construct($input, $output=null) {
    $this->input_ = $input;
    $this->output_ = $output ? $output : $input;
  }

  public function getUrlByUid($uid)
  {
    $this->send_getUrlByUid($uid);
    return $this->recv_getUrlByUid();
  }

  public function send_getUrlByUid($uid)
  {
    $args = new \Provider\MerchantStore\MerchantStore_getUrlByUid_args();
    $args->uid = $uid;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'getUrlByUid', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('getUrlByUid', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_getUrlByUid()
  {
    $bin_accel = ($this->input_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\Provider\MerchantStore\MerchantStore_getUrlByUid_result', $this->input_->isStrictRead());
    else
    {
      $rseqid = 0;
      $fname = null;
      $mtype = 0;

      $this->input_->readMessageBegin($fname, $mtype, $rseqid);
      if ($mtype == TMessageType::EXCEPTION) {
        $x = new TApplicationException();
        $x->read($this->input_);
        $this->input_->readMessageEnd();
        throw $x;
      }
      $result = new \Provider\MerchantStore\MerchantStore_getUrlByUid_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    throw new \Exception("getUrlByUid failed: unknown result");
  }

  public function getStoreLinkByStoreId($ids)
  {
    $this->send_getStoreLinkByStoreId($ids);
    return $this->recv_getStoreLinkByStoreId();
  }

  public function send_getStoreLinkByStoreId($ids)
  {
    $args = new \Provider\MerchantStore\MerchantStore_getStoreLinkByStoreId_args();
    $args->ids = $ids;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'getStoreLinkByStoreId', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('getStoreLinkByStoreId', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_getStoreLinkByStoreId()
  {
    $bin_accel = ($this->input_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\Provider\MerchantStore\MerchantStore_getStoreLinkByStoreId_result', $this->input_->isStrictRead());
    else
    {
      $rseqid = 0;
      $fname = null;
      $mtype = 0;

      $this->input_->readMessageBegin($fname, $mtype, $rseqid);
      if ($mtype == TMessageType::EXCEPTION) {
        $x = new TApplicationException();
        $x->read($this->input_);
        $this->input_->readMessageEnd();
        throw $x;
      }
      $result = new \Provider\MerchantStore\MerchantStore_getStoreLinkByStoreId_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    throw new \Exception("getStoreLinkByStoreId failed: unknown result");
  }

  public function getStoreInfoByStoreIds($ids)
  {
    $this->send_getStoreInfoByStoreIds($ids);
    return $this->recv_getStoreInfoByStoreIds();
  }

  public function send_getStoreInfoByStoreIds($ids)
  {
    $args = new \Provider\MerchantStore\MerchantStore_getStoreInfoByStoreIds_args();
    $args->ids = $ids;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'getStoreInfoByStoreIds', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('getStoreInfoByStoreIds', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_getStoreInfoByStoreIds()
  {
    $bin_accel = ($this->input_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\Provider\MerchantStore\MerchantStore_getStoreInfoByStoreIds_result', $this->input_->isStrictRead());
    else
    {
      $rseqid = 0;
      $fname = null;
      $mtype = 0;

      $this->input_->readMessageBegin($fname, $mtype, $rseqid);
      if ($mtype == TMessageType::EXCEPTION) {
        $x = new TApplicationException();
        $x->read($this->input_);
        $this->input_->readMessageEnd();
        throw $x;
      }
      $result = new \Provider\MerchantStore\MerchantStore_getStoreInfoByStoreIds_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    throw new \Exception("getStoreInfoByStoreIds failed: unknown result");
  }

}

// HELPER FUNCTIONS AND STRUCTURES

class MerchantStore_getUrlByUid_args {
  static $_TSPEC;

  public $uid = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'uid',
          'type' => TType::I64,
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['uid'])) {
        $this->uid = $vals['uid'];
      }
    }
  }

  public function getName() {
    return 'MerchantStore_getUrlByUid_args';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::I64) {
            $xfer += $input->readI64($this->uid);
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('MerchantStore_getUrlByUid_args');
    if ($this->uid !== null) {
      $xfer += $output->writeFieldBegin('uid', TType::I64, 1);
      $xfer += $output->writeI64($this->uid);
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class MerchantStore_getUrlByUid_result {
  static $_TSPEC;

  public $success = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        0 => array(
          'var' => 'success',
          'type' => TType::MAP,
          'ktype' => TType::STRING,
          'vtype' => TType::STRING,
          'key' => array(
            'type' => TType::STRING,
          ),
          'val' => array(
            'type' => TType::STRING,
            ),
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['success'])) {
        $this->success = $vals['success'];
      }
    }
  }

  public function getName() {
    return 'MerchantStore_getUrlByUid_result';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 0:
          if ($ftype == TType::MAP) {
            $this->success = array();
            $_size0 = 0;
            $_ktype1 = 0;
            $_vtype2 = 0;
            $xfer += $input->readMapBegin($_ktype1, $_vtype2, $_size0);
            for ($_i4 = 0; $_i4 < $_size0; ++$_i4)
            {
              $key5 = '';
              $val6 = '';
              $xfer += $input->readString($key5);
              $xfer += $input->readString($val6);
              $this->success[$key5] = $val6;
            }
            $xfer += $input->readMapEnd();
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('MerchantStore_getUrlByUid_result');
    if ($this->success !== null) {
      if (!is_array($this->success)) {
        throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
      }
      $xfer += $output->writeFieldBegin('success', TType::MAP, 0);
      {
        $output->writeMapBegin(TType::STRING, TType::STRING, count($this->success));
        {
          foreach ($this->success as $kiter7 => $viter8)
          {
            $xfer += $output->writeString($kiter7);
            $xfer += $output->writeString($viter8);
          }
        }
        $output->writeMapEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class MerchantStore_getStoreLinkByStoreId_args {
  static $_TSPEC;

  public $ids = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'ids',
          'type' => TType::MAP,
          'ktype' => TType::I64,
          'vtype' => TType::I64,
          'key' => array(
            'type' => TType::I64,
          ),
          'val' => array(
            'type' => TType::I64,
            ),
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['ids'])) {
        $this->ids = $vals['ids'];
      }
    }
  }

  public function getName() {
    return 'MerchantStore_getStoreLinkByStoreId_args';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::MAP) {
            $this->ids = array();
            $_size9 = 0;
            $_ktype10 = 0;
            $_vtype11 = 0;
            $xfer += $input->readMapBegin($_ktype10, $_vtype11, $_size9);
            for ($_i13 = 0; $_i13 < $_size9; ++$_i13)
            {
              $key14 = 0;
              $val15 = 0;
              $xfer += $input->readI64($key14);
              $xfer += $input->readI64($val15);
              $this->ids[$key14] = $val15;
            }
            $xfer += $input->readMapEnd();
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('MerchantStore_getStoreLinkByStoreId_args');
    if ($this->ids !== null) {
      if (!is_array($this->ids)) {
        throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
      }
      $xfer += $output->writeFieldBegin('ids', TType::MAP, 1);
      {
        $output->writeMapBegin(TType::I64, TType::I64, count($this->ids));
        {
          foreach ($this->ids as $kiter16 => $viter17)
          {
            $xfer += $output->writeI64($kiter16);
            $xfer += $output->writeI64($viter17);
          }
        }
        $output->writeMapEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class MerchantStore_getStoreLinkByStoreId_result {
  static $_TSPEC;

  public $success = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        0 => array(
          'var' => 'success',
          'type' => TType::MAP,
          'ktype' => TType::I64,
          'vtype' => TType::MAP,
          'key' => array(
            'type' => TType::I64,
          ),
          'val' => array(
            'type' => TType::MAP,
            'ktype' => TType::STRING,
            'vtype' => TType::STRING,
            'key' => array(
              'type' => TType::STRING,
            ),
            'val' => array(
              'type' => TType::STRING,
              ),
            ),
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['success'])) {
        $this->success = $vals['success'];
      }
    }
  }

  public function getName() {
    return 'MerchantStore_getStoreLinkByStoreId_result';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 0:
          if ($ftype == TType::MAP) {
            $this->success = array();
            $_size18 = 0;
            $_ktype19 = 0;
            $_vtype20 = 0;
            $xfer += $input->readMapBegin($_ktype19, $_vtype20, $_size18);
            for ($_i22 = 0; $_i22 < $_size18; ++$_i22)
            {
              $key23 = 0;
              $val24 = array();
              $xfer += $input->readI64($key23);
              $val24 = array();
              $_size25 = 0;
              $_ktype26 = 0;
              $_vtype27 = 0;
              $xfer += $input->readMapBegin($_ktype26, $_vtype27, $_size25);
              for ($_i29 = 0; $_i29 < $_size25; ++$_i29)
              {
                $key30 = '';
                $val31 = '';
                $xfer += $input->readString($key30);
                $xfer += $input->readString($val31);
                $val24[$key30] = $val31;
              }
              $xfer += $input->readMapEnd();
              $this->success[$key23] = $val24;
            }
            $xfer += $input->readMapEnd();
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('MerchantStore_getStoreLinkByStoreId_result');
    if ($this->success !== null) {
      if (!is_array($this->success)) {
        throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
      }
      $xfer += $output->writeFieldBegin('success', TType::MAP, 0);
      {
        $output->writeMapBegin(TType::I64, TType::MAP, count($this->success));
        {
          foreach ($this->success as $kiter32 => $viter33)
          {
            $xfer += $output->writeI64($kiter32);
            {
              $output->writeMapBegin(TType::STRING, TType::STRING, count($viter33));
              {
                foreach ($viter33 as $kiter34 => $viter35)
                {
                  $xfer += $output->writeString($kiter34);
                  $xfer += $output->writeString($viter35);
                }
              }
              $output->writeMapEnd();
            }
          }
        }
        $output->writeMapEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class MerchantStore_getStoreInfoByStoreIds_args {
  static $_TSPEC;

  public $ids = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'ids',
          'type' => TType::MAP,
          'ktype' => TType::I64,
          'vtype' => TType::I64,
          'key' => array(
            'type' => TType::I64,
          ),
          'val' => array(
            'type' => TType::I64,
            ),
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['ids'])) {
        $this->ids = $vals['ids'];
      }
    }
  }

  public function getName() {
    return 'MerchantStore_getStoreInfoByStoreIds_args';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 1:
          if ($ftype == TType::MAP) {
            $this->ids = array();
            $_size36 = 0;
            $_ktype37 = 0;
            $_vtype38 = 0;
            $xfer += $input->readMapBegin($_ktype37, $_vtype38, $_size36);
            for ($_i40 = 0; $_i40 < $_size36; ++$_i40)
            {
              $key41 = 0;
              $val42 = 0;
              $xfer += $input->readI64($key41);
              $xfer += $input->readI64($val42);
              $this->ids[$key41] = $val42;
            }
            $xfer += $input->readMapEnd();
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('MerchantStore_getStoreInfoByStoreIds_args');
    if ($this->ids !== null) {
      if (!is_array($this->ids)) {
        throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
      }
      $xfer += $output->writeFieldBegin('ids', TType::MAP, 1);
      {
        $output->writeMapBegin(TType::I64, TType::I64, count($this->ids));
        {
          foreach ($this->ids as $kiter43 => $viter44)
          {
            $xfer += $output->writeI64($kiter43);
            $xfer += $output->writeI64($viter44);
          }
        }
        $output->writeMapEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class MerchantStore_getStoreInfoByStoreIds_result {
  static $_TSPEC;

  public $success = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        0 => array(
          'var' => 'success',
          'type' => TType::MAP,
          'ktype' => TType::STRING,
          'vtype' => TType::STRING,
          'key' => array(
            'type' => TType::STRING,
          ),
          'val' => array(
            'type' => TType::STRING,
            ),
          ),
        );
    }
    if (is_array($vals)) {
      if (isset($vals['success'])) {
        $this->success = $vals['success'];
      }
    }
  }

  public function getName() {
    return 'MerchantStore_getStoreInfoByStoreIds_result';
  }

  public function read($input)
  {
    $xfer = 0;
    $fname = null;
    $ftype = 0;
    $fid = 0;
    $xfer += $input->readStructBegin($fname);
    while (true)
    {
      $xfer += $input->readFieldBegin($fname, $ftype, $fid);
      if ($ftype == TType::STOP) {
        break;
      }
      switch ($fid)
      {
        case 0:
          if ($ftype == TType::MAP) {
            $this->success = array();
            $_size45 = 0;
            $_ktype46 = 0;
            $_vtype47 = 0;
            $xfer += $input->readMapBegin($_ktype46, $_vtype47, $_size45);
            for ($_i49 = 0; $_i49 < $_size45; ++$_i49)
            {
              $key50 = '';
              $val51 = '';
              $xfer += $input->readString($key50);
              $xfer += $input->readString($val51);
              $this->success[$key50] = $val51;
            }
            $xfer += $input->readMapEnd();
          } else {
            $xfer += $input->skip($ftype);
          }
          break;
        default:
          $xfer += $input->skip($ftype);
          break;
      }
      $xfer += $input->readFieldEnd();
    }
    $xfer += $input->readStructEnd();
    return $xfer;
  }

  public function write($output) {
    $xfer = 0;
    $xfer += $output->writeStructBegin('MerchantStore_getStoreInfoByStoreIds_result');
    if ($this->success !== null) {
      if (!is_array($this->success)) {
        throw new TProtocolException('Bad type in structure.', TProtocolException::INVALID_DATA);
      }
      $xfer += $output->writeFieldBegin('success', TType::MAP, 0);
      {
        $output->writeMapBegin(TType::STRING, TType::STRING, count($this->success));
        {
          foreach ($this->success as $kiter52 => $viter53)
          {
            $xfer += $output->writeString($kiter52);
            $xfer += $output->writeString($viter53);
          }
        }
        $output->writeMapEnd();
      }
      $xfer += $output->writeFieldEnd();
    }
    $xfer += $output->writeFieldStop();
    $xfer += $output->writeStructEnd();
    return $xfer;
  }

}

class MerchantStoreProcessor {
  protected $handler_ = null;
  public function __construct($handler) {
    $this->handler_ = $handler;
  }

  public function process($input, $output) {
    $rseqid = 0;
    $fname = null;
    $mtype = 0;

    $input->readMessageBegin($fname, $mtype, $rseqid);
    $methodname = 'process_'.$fname;
    if (!method_exists($this, $methodname)) {
      $input->skip(TType::STRUCT);
      $input->readMessageEnd();
      $x = new TApplicationException('Function '.$fname.' not implemented.', TApplicationException::UNKNOWN_METHOD);
      $output->writeMessageBegin($fname, TMessageType::EXCEPTION, $rseqid);
      $x->write($output);
      $output->writeMessageEnd();
      $output->getTransport()->flush();
      return;
    }
    $this->$methodname($rseqid, $input, $output);
    return true;
  }

  protected function process_getUrlByUid($seqid, $input, $output) {
    $args = new \Provider\MerchantStore\MerchantStore_getUrlByUid_args();
    $args->read($input);
    $input->readMessageEnd();
    $result = new \Provider\MerchantStore\MerchantStore_getUrlByUid_result();
    $result->success = $this->handler_->getUrlByUid($args->uid);
    $bin_accel = ($output instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($output, 'getUrlByUid', TMessageType::REPLY, $result, $seqid, $output->isStrictWrite());
    }
    else
    {
      $output->writeMessageBegin('getUrlByUid', TMessageType::REPLY, $seqid);
      $result->write($output);
      $output->writeMessageEnd();
      $output->getTransport()->flush();
    }
  }
  protected function process_getStoreLinkByStoreId($seqid, $input, $output) {
    $args = new \Provider\MerchantStore\MerchantStore_getStoreLinkByStoreId_args();
    $args->read($input);
    $input->readMessageEnd();
    $result = new \Provider\MerchantStore\MerchantStore_getStoreLinkByStoreId_result();
    $result->success = $this->handler_->getStoreLinkByStoreId($args->ids);
    $bin_accel = ($output instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($output, 'getStoreLinkByStoreId', TMessageType::REPLY, $result, $seqid, $output->isStrictWrite());
    }
    else
    {
      $output->writeMessageBegin('getStoreLinkByStoreId', TMessageType::REPLY, $seqid);
      $result->write($output);
      $output->writeMessageEnd();
      $output->getTransport()->flush();
    }
  }
  protected function process_getStoreInfoByStoreIds($seqid, $input, $output) {
    $args = new \Provider\MerchantStore\MerchantStore_getStoreInfoByStoreIds_args();
    $args->read($input);
    $input->readMessageEnd();
    $result = new \Provider\MerchantStore\MerchantStore_getStoreInfoByStoreIds_result();
    $result->success = $this->handler_->getStoreInfoByStoreIds($args->ids);
    $bin_accel = ($output instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($output, 'getStoreInfoByStoreIds', TMessageType::REPLY, $result, $seqid, $output->isStrictWrite());
    }
    else
    {
      $output->writeMessageBegin('getStoreInfoByStoreIds', TMessageType::REPLY, $seqid);
      $result->write($output);
      $output->writeMessageEnd();
      $output->getTransport()->flush();
    }
  }
}
