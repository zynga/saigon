<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class FileUtils
{

    /**
     * returnContents - return the contents of the file specified
     * 
     * @param mixed $file file we are attempting to read and return
     *
     * @static
     * @access public
     * @return void
     */
    public static function returnContents($file)
    {
        if ((!file_exists($file)) || (!is_readable($file))) {
            return false;
        }
        return file_get_contents($file);
    }

    /**
     * returnContentsMD5 - return the md5 sum of the contents of the file specified
     * 
     * @param mixed $file file we are attempting to read and md5 sum
     *
     * @static
     * @access public
     * @return void
     */
    public static function returnContentsMD5($file)
    {
        if ((!file_exists($file)) || (!is_readable($file))) {
            return false;
        }
        return md5(file_get_contents($file));
    }

    /**
     * writeFile - write file with specified contents and make sure md5 sum matches
     * 
     * @param mixed $file     file we are attempting to write too
     * @param mixed $contents contents of the file we are writing
     * @param mixed $md5      md5 sum of the contents of the file
     *
     * @static
     * @access public
     * @return void
     */
    public static function writeFile($file, $contents, $md5)
    {
        file_put_contents($file, $contents);
        /* Verify contents md5 match */
        $verifyContents = file_get_contents($file);
        $md5_verifyContents = md5($verifyContents);
        if ($md5 != $md5_verifyContents) {
            return false;
        }
        return true;
    }

    /**
     * chownFile - change the ownership of the file specified
     * 
     * @param mixed  $file  file we are attempting to chown
     * @param string $owner owner we are changing the file ownership too
     * @param string $group group we are changing the file ownership too
     *
     * @static
     * @access public
     * @return void
     */
    public static function chownFile($file, $owner, $group = false)
    {
        if ($group !== false) {
            $command = "/bin/chown $owner:$group $file";
        } else {
            $command = "/bin/chown $owner $file";
        }
        exec($command.' 2>&1', $output, $exitCode);
        if ($exitCode != 0) {
            return implode("\n", $output);
        }
        return true;
    }

    /**
     * chmodFile - change mode of the file specified
     * 
     * @param mixed $file file we are attempting to chmod
     * @param int   $mode file mode we are attempting to set
     *
     * @static
     * @access public
     * @return void
     */
    public static function chmodFile($file, $mode)
    {
        if (!preg_match("/^\d{3,4}$/", $mode)) {
            return false;
        }
        $command = "/bin/chmod $mode $file";
        exec($command.' 2>&1', $output, $exitCode);
        if ($exitCode != 0) {
            return implode("\n", $output);
        }
        return true;
    }

    /**
     * moveFile - move file from source location to destination 
     * 
     * @param mixed $srcFile  source file we are attempting to move
     * @param mixed $destFile destination file we are wanting to move the file too
     * @param mixed $verbose  verbosely print the output of the commands running
     *
     * @static
     * @access public
     * @return void
     */
    public static function moveFile($srcFile, $destFile, $verbose = false)
    {
        $command = "/bin/mv -f -u -v $srcFile $destFile";
        exec($command.' 2>&1', $output, $exitCode);
        if ($verbose) {
            print "Moving File: ".implode("\n", $output)."\n";
        }
        if ($exitCode != 0) {
            return implode("\n", $output);
        }
        return true;
    }

}

