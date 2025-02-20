<?php

namespace App\Services\Minecraft;

class MinecraftPing
{
    private $Socket;
    private $ServerAddress;
    private $ServerPort;
    private $Timeout;

    public function __construct($Address, $Port = 25565, $Timeout = 2, $ResolveSRV = true)
    {
        $this->ServerAddress = $Address;
        $this->ServerPort = (int)$Port;
        $this->Timeout = (int)$Timeout;

        if ($ResolveSRV) {
            $this->ResolveSRV();
        }

        $this->Connect();
    }

    public function __destruct()
    {
        $this->Close();
    }

    public function Close()
    {
        if ($this->Socket !== null) {
            fclose($this->Socket);
            $this->Socket = null;
        }
    }

    public function Connect()
    {
        $connectTimeout = $this->Timeout;
        $this->Socket = @fsockopen($this->ServerAddress, $this->ServerPort, $errno, $errstr, $connectTimeout);

        if (!$this->Socket) {
            throw new MinecraftPingException("Failed to connect or create a socket: $errno ($errstr)");
        }

        stream_set_timeout($this->Socket, $this->Timeout);
    }

    public function Query()
    {
        $TimeStart = microtime(true); // for read timeout purposes

        // See http://wiki.vg/Protocol (Status Ping)
        $Data = "\x00"; // packet ID = 0 (varint)

        $Data .= "\x04"; // Protocol version (varint)
        $Data .= pack('c', strlen($this->ServerAddress)) . $this->ServerAddress; // Server (varint len + UTF-8 addr)
        $Data .= pack('n', $this->ServerPort); // Server port (unsigned short)
        $Data .= "\x01"; // Next state: status (varint)

        $Data = pack('c', strlen($Data)) . $Data; // prepend length of packet ID + data

        fwrite($this->Socket, $Data); // handshake
        fwrite($this->Socket, "\x01\x00"); // status ping

        $Length = $this->ReadVarInt(); // full packet length

        if ($Length < 10) {
            return false;
        }

        $this->ReadVarInt(); // packet type, in server ping it's 0

        $Length = $this->ReadVarInt(); // string length

        $Data = "";
        do {
            if (microtime(true) - $TimeStart > $this->Timeout) {
                throw new MinecraftPingException('Server read timed out');
            }

            $Remainder = $Length - strlen($Data);
            $block = fread($this->Socket, $Remainder); // and finally the json string
            // abort if there is no progress
            if (!$block) {
                throw new MinecraftPingException('Server returned too few data');
            }

            $Data .= $block;
        } while (strlen($Data) < $Length);

        $Data = json_decode($Data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new MinecraftPingException('JSON parsing failed');
        }

        return $Data;
    }

    private function ReadVarInt()
    {
        $i = 0;
        $j = 0;

        while (true) {
            $k = @fgetc($this->Socket);

            if ($k === FALSE) {
                return 0;
            }

            $k = ord($k);

            $i |= ($k & 0x7F) << $j++ * 7;

            if ($j > 5) {
                throw new MinecraftPingException('VarInt too big');
            }

            if (($k & 0x80) != 128) {
                break;
            }
        }

        return $i;
    }

    private function ResolveSRV()
    {
        if (ip2long($this->ServerAddress) !== false) {
            return;
        }

        $Record = @dns_get_record('_minecraft._tcp.' . $this->ServerAddress, DNS_SRV);

        if (empty($Record)) {
            return;
        }

        if (isset($Record[0]['target'])) {
            $this->ServerAddress = $Record[0]['target'];
        }

        if (isset($Record[0]['port'])) {
            $this->ServerPort = $Record[0]['port'];
        }
    }
} 