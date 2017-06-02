<?php

namespace CleverAge\EAVManager\ProcessBundle\Process\Generic;

use CleverAge\EAVManager\ProcessBundle\Process\ProcessInterface;
use phpseclib\Net\SFTP;

/**
 * @TODO describe class usage
 */
class SFTPFileCopyProcess implements ProcessInterface
{

    /** @var string[] */
    protected $filePaths;

    /** @var string */
    protected $host;

    /** @var string */
    protected $login;

    /** @var string */
    protected $password;

    /** @var string */
    protected $destination;

    /**
     * SFTPFileCopyProcess constructor.
     *
     * @param string $host
     * @param string $login
     * @param string $password
     * @param string $destination
     */
    public function __construct($host, $login, $password, $destination)
    {
        $this->host = $host;
        $this->login = $login;
        $this->password = $password;
        $this->destination = $destination;
    }


    /**
     * {@inheritdoc}
     */
    public function setInput($data)
    {
        $this->filePaths = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $sftp = new SFTP($this->host);
        if (!$sftp->login($this->login, $this->password)) {
            throw new \Exception("Cannot login into {$this->login}@{$this->host}");
        }

        $destinationParts = explode('/', $this->destination);

        foreach ($this->filePaths as $filePath) {
            $remoteFileParts = $destinationParts;
            $remoteFileParts[] = basename($filePath);
            $remoteFileParts = array_filter($remoteFileParts);
            $remoteFile = implode('/', $remoteFileParts);

            $result = $sftp->put($remoteFile, $filePath, SFTP::SOURCE_LOCAL_FILE);

            if (!$result) {
                throw new \Exception("Could not upload {$filePath} into {$remoteFile}");
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOutput()
    {
        return $this->filePaths;
    }

}
