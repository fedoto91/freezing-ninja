<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * DNS Library for handling lookups and updates. 
 *
 * PHP Version 5
 *
 * Copyright (c) 2010, Mike Pultz <mike@mikepultz.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Mike Pultz nor the names of his contributors 
 *     may be used to endorse or promote products derived from this 
 *     software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  Networking
 * @package   Net_DNS2
 * @author    Mike Pultz <mike@mikepultz.com>
 * @copyright 2010 Mike Pultz <mike@mikepultz.com>
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id: Streams.php 107 2011-06-26 07:44:20Z mike.pultz $
 * @link      http://pear.php.net/package/Net_DNS2
 * @since     File available since Release 0.6.0
 *
 */

/**
 * Socket handling class using the PHP Streams
 *
 * The sockets extension is faster than the stream functions in PHP, but it's
 * not standard. So if the extension is loaded, then the Net_DNS_Socket_Sockets
 * class it used, otherwise, this class it used.
 *   
 * @category Networking
 * @package  Net_DNS2
 * @author   Mike Pultz <mike@mikepultz.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link     http://pear.php.net/package/Net_DNS2
 * @see      Net_DNS2_Socket
 *
 */
class Net_DNS2_Socket_Streams extends Net_DNS2_Socket
{
    private $_context;

    /**
     * opens a socket connection to the DNS server
     *
     * @return boolean
     * @access public
     *
     */
    public function open()
    {
        //
        // create a list of options for the context 
        //
        $opts = array('socket' => array());
        
        //
        // bind to a local IP/port if it's set
        //
        if (strlen($this->local_host) > 0) {

            $opts['socket']['bindto'] = $this->local_host;
            if ($this->local_port > 0) {

                $opts['socket']['bindto'] .= ':' . $this->local_port;
            }
        }

        //
        // create the context
        //
        $this->_context = @stream_context_create($opts);

        //
        // create socket
        //
        $errno;
        $errstr;

        switch($this->type) {
        case SOCK_STREAM:

            if (Net_DNS2::isIPv4($this->host) == true) {

                $this->_sock = @stream_socket_client(
                    'tcp://' . $this->host . ':' . $this->port, 
                    $errno, $errstr, $this->timeout, 
                    STREAM_CLIENT_CONNECT, $this->_context
                );
            } else if (Net_DNS2::isIPv6($this->host) == true) {

                $this->_sock = @stream_socket_client(
                    'tcp://[' . $this->host . ']:' . $this->port, 
                    $errno, $errstr, $this->timeout, 
                    STREAM_CLIENT_CONNECT, $this->_context
                );
            } else {

                $this->last_error = 'invalid address type: ' . $this->host;
                return false;
            }

            break;
        
        case SOCK_DGRAM:

            if (Net_DNS2::isIPv4($this->host) == true) {

                $this->_sock = @stream_socket_client(
                    'udp://' . $this->host . ':' . $this->port, 
                    $errno, $errstr, $this->timeout, 
                    STREAM_CLIENT_CONNECT, $this->_context
                );
            } else if (Net_DNS2::isIPv6($this->host) == true) {

                $this->_sock = @stream_socket_client(
                    'udp://[' . $this->host . ']:' . $this->port, 
                    $errno, $errstr, $this->timeout, 
                    STREAM_CLIENT_CONNECT, $this->_context
                );
            } else {

                $this->last_error = 'invalid address type: ' . $this->host;
                return false;
            }

            break;
            
        default:
            $this->last_error = 'Invalid socket type: ' . $this->type;
            return false;
        }

        if ($this->_sock === false) {

            $this->last_error = $errstr;
            return false;
        }

        //
        // set it to non-blocking and set the timeout
        //
        @stream_set_blocking($this->_sock, 0);
        @stream_set_timeout($this->_sock, $this->timeout);

        return true;
    }

    /**
     * closes a socket connection to the DNS server
     *
     * @return boolean
     * @access public
     *
     */
    public function close()
    {
        if (is_resource($this->_sock) === true) {

            @fclose($this->_sock);
        }
        return true;
    }

    /**
     * writes the given string to the DNS server socket
     *
     * @param string $data a binary packed DNS packet
     *
     * @return boolean
     * @access public
     *
     */
    public function write($data)
    {
        $length = strlen($data);
        if ($length == 0) {

            $this->last_error = 'empty data on write()';
            return false;
        }

        $read   = null;
        $write  = array($this->_sock);
        $except = null;

        //
        // select on write
        //
        switch(@stream_select($read, $write, $except, $this->timeout)) {
        case false:
            $this->last_error = 'failed on stream_select()';
            return false;
            break;
       
        case 0:
            return false;
            break;
            
        default:
            ;
        }

        //
        // if it's a TCP socket, then we need to packet and send the length of the
        // data as the first 16bit of data.
        //        
        if ($this->type == SOCK_STREAM) {

            $s = chr($length >> 8) . chr($length);

            if (@fwrite($this->_sock, $s) === false) {

                $this->last_error = 'failed to fwrite() 16bit length';
                return false;
            }
        }

        //
        // write the data to the socket
        //
        $size = @fwrite($this->_sock, $data);
        if ( ($size === false) || ($size != $length) ) {
        
            $this->last_error = 'failed to fwrite() packet';
            return false;
        }

        return true;
    }

    /**
     * reads a response from a DNS server
     *
     * @param integer &$size the size of the DNS packet read is passed back
     *
     * @return mixed         returns the data on success and false on error
     * @access public
     *
     */
    public function read(&$size)
    {
        $read   = array($this->_sock);
        $write  = null;
        $except = null;

        //
        // select on read
        //
        switch(stream_select($read, $write, $except, $this->timeout)) {
        case false:
            $this->last_error = 'error on stream_select()';
            return false;
            break;
            
        case 0:
            return false;
            break;
            
        default:
            ;
        }

        $data = '';
        $length = Net_DNS2_Lookups::DNS_MAX_UDP_SIZE;

        //
        // if it's a TCP socket, then the first two bytes is the length of the DNS
        // packet- we need to read that off first, then use that value for the    
        // packet read.
        //
        if ($this->type == SOCK_STREAM) {
    
            if (($data = fread($this->_sock, 2)) === false) {
                
                $this->last_error = 'failed on fread() for data length';
                return false;
            }
        
            $length = ord($data[0]) << 8 | ord($data[1]);
            if ($length < Net_DNS2_Lookups::DNS_HEADER_SIZE) {

                return false;
            }
        }

        //
        // read the data from the socket
        //
        if (($data = fread($this->_sock, $length)) === false) {
            
            $this->last_error = 'failed on fread() for data';
            return false;
        }
        
        $size = strlen($data);

        return $data;
    }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
