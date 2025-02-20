<?php

namespace App\Services\Minecraft;

class MinecraftQuery
{
    /*
     * Class written by xPaw
     *
     * Website: http://xpaw.me
     * GitHub: https://github.com/xPaw/PHP-Minecraft-Query
     */

    const STATISTIC = 0x00;
    const HANDSHAKE = 0x09;

    private $Socket;
    private $Players;
    private $Info;

    public function Connect($Ip, $Port = 25565, $Timeout = 3, $ResolveSRV = true)
    {
        if ($ResolveSRV) {
            $result = @dns_get_record('_minecraft._tcp.' . $Ip, DNS_SRV);

            if (!empty($result[0]['target'])) {
                $Ip = $result[0]['target'];
            }
        }

        $this->Socket = @fsockopen('udp://' . $Ip, (int)$Port, $ErrNo, $ErrStr, $Timeout);

        if ($ErrNo || $this->Socket === false) {
            throw new MinecraftQueryException('Could not create socket: ' . $ErrStr);
        }

        stream_set_timeout($this->Socket, $Timeout);
        stream_set_blocking($this->Socket, true);

        try {
            $Challenge = $this->GetChallenge();
            $this->GetStatus($Challenge);
        } finally {
            fclose($this->Socket);
        }

        return true;
    }

    public function GetInfo()
    {
        return isset($this->Info) ? $this->Info : false;
    }

    public function GetPlayers()
    {
        return isset($this->Players) ? $this->Players : false;
    }

    private function GetChallenge()
    {
        $Data = $this->WriteData(self::HANDSHAKE);

        if ($Data === false) {
            throw new MinecraftQueryException('Failed to receive challenge.');
        }

        return pack('N', $Data);
    }

    private function GetStatus($Challenge)
    {
        $Data = $this->WriteData(self::STATISTIC, $Challenge . pack('c*', 0x00, 0x00, 0x00, 0x00));

        if (!$Data) {
            throw new MinecraftQueryException('Failed to receive status.');
        }

        $Last = '';
        $Info = [];

        $Data = substr($Data, 11);
        $Data = explode("\x00\x00\x01player_\x00\x00", $Data);

        if (count($Data) !== 2) {
            throw new MinecraftQueryException('Failed to parse server\'s response.');
        }

        $Players = substr($Data[1], 0, -2);
        $Data = explode("\x00", $Data[0]);

        // Array with known keys in order to validate the result
        // It can happen that server sends custom strings containing bad things (who can know!)
        $Keys = [
            'hostname'   => 'HostName',
            'gametype'   => 'GameType',
            'version'    => 'Version',
            'plugins'    => 'Plugins',
            'map'        => 'Map',
            'numplayers' => 'Players',
            'maxplayers' => 'MaxPlayers',
            'hostport'   => 'HostPort',
            'hostip'     => 'HostIp',
            'game_id'    => 'GameName'
        ];

        foreach ($Data as $Key => $Value) {
            if (~$Key & 1) {
                if (!array_key_exists($Value, $Keys)) {
                    $Last = false;
                    continue;
                }

                $Last = $Keys[$Value];
                continue;
            }

            if ($Last != false) {
                $Info[$Last] = $Value;
            }
        }

        // Ints
        $Info['Players']    = intval($Info['Players']);
        $Info['MaxPlayers'] = intval($Info['MaxPlayers']);
        $Info['HostPort']   = intval($Info['HostPort']);

        // Parse "plugins" if any
        if (isset($Info['Plugins'])) {
            $Data = explode(": ", $Info['Plugins'], 2);

            $Info['RawPlugins'] = $Info['Plugins'];
            $Info['Software']   = $Data[0];

            if (count($Data) == 2) {
                $Info['Plugins'] = explode("; ", $Data[1]);
            }
        }

        $this->Info = $Info;

        if ($Players) {
            $this->Players = explode("\x00", $Players);
        }
    }

    private function WriteData($Command, $Append = "")
    {
        $Command = pack('c*', 0xFE, 0xFD, $Command, 0x01, 0x02, 0x03, 0x04) . $Append;
        $Length  = strlen($Command);

        if ($Length !== fwrite($this->Socket, $Command, $Length)) {
            throw new MinecraftQueryException("Failed to write on socket.");
        }

        $Data = fread($this->Socket, 4096);

        if ($Data === false) {
            throw new MinecraftQueryException("Failed to read from socket.");
        }

        if (strlen($Data) < 5 || $Data[0] != $Command[2]) {
            return false;
        }

        return substr($Data, 5);
    }
} 