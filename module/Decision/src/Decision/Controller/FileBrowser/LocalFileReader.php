<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Decision\Controller\FileBrowser;

/**
 * Description of LocalFileReader
 *
 * @author s134399
 */
class LocalFileReader implements FileReader
{

    /**
     * The location in the local filesystem that is considered the 'root' for this browser
     * @var string
     */
    static private $root;

    /**
     * A regex pattern matching all valid filepaths.
     * @var string
     */
    static private $validFilepath;

    public function __construct($root, $validFilepath)
    {
        $this->root = $root;
        $this->validFilepath = $validFilepath;
    }

    public function downloadFile($path)
    {
        $fullPath = $this->root . $path;
        if (!is_file($fullPath) || !$this->isValidPathName($fullPath)) {
            return null;
        }
        $content_type = 'octet-stream';
        if (mime_content_type($fullPath)==='text/plain') {
            $content_type = 'text/plain';
        }
        $response = new \Zend\Http\Response\Stream();
        $response->setStream(fopen('file://' . $fullPath, 'r'));
        $response->setStatusCode(200);
        $headers = new \Zend\Http\Headers();
        $headers->addHeaderLine('Content-Type', $content_type)
                ->addHeaderLine('Content-Disposition', 'filename="' . end(explode('/', $fullPath)) . '"')
                ->addHeaderLine('Content-Length', filesize($fullPath));
        $response->setHeaders($headers);
        return $response;
    }

    public function listDir($path)
    {
        //remove the trailing slash from the dir
        $fullPath = $this->root . $path;
        if (!is_dir($fullPath)) {
            return null;
        }
        //We can insert an additional /, except when when $path is the root
        $delimiter = $path !== '' ? '/' : '';
        $dircontents = scandir($fullPath);
        $files = [];
        foreach ($dircontents as $dircontent) {
            if ($dircontent[0]==='.') {
                continue;
            }
            if (is_dir($fullPath . '/' . $dircontent)) {
                $kind = 'dir';
            } elseif (is_file($fullPath . '/' . $dircontent)) {
                $kind = 'file';
            } else {
                //Ignore all strange filesystem thingies like symlinks and such
                continue;
            }
            if (!$this->isValidPathName($fullPath . '/' . $dircontent)) {
                //don't display invalid pathnames
                continue;
            }
            $files[] = new FileNode(
                $kind,
                $path . $delimiter . $dircontent,
                $dircontent
            );
        }
        return $files;
    }

    public function isDir($path)
    {
        return is_dir($this->root . $path);
    }

    public function isAllowed($path)
    {
        $fullPath = $this->root . $path;
        if (!is_readable($fullPath) || !$this->isValidPathName($path)) {
            return false;
        }
        $realFullPath = realpath($fullPath);
        $realRoot = realpath($this->root);
        //Check whether the real location of fullPath is in a subdir of our 'root'.
        if (substr($realFullPath, 0, strlen($realRoot)) !== $realRoot) {
            return false;
        }
        return true;
    }

    protected function isValidPathName($path)
    {
        $res = preg_match('#^' . $this->validFilepath . '$#', $path) === 1;
        return $res;
    }
}
