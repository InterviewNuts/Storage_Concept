<?php

class Util 
{
    public static function readMailiId(&$toMailId,$tomail)
    {
        $flag=false;

        //! check for the platfor. Though this script is written only for Linux, But platform is taken care
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
        {
            $confFile="config/slaConfig.cfg";

            if( file_exists($confFile))
            {
                $flag=true;
            }
        }
        else
        {
            $confFile="./config/slaConfig.cfg";

            if( file_exists($confFile))
            {
                $flag=true;
            }
        }

        //! If file exist
        if($flag)
        {
            $fileObj = new SplFileObject($confFile);
            $str='';
            while (!$fileObj->eof())
            {
                $line=$fileObj->fgets();
                //! ignore the '#'
                if( substr($line,0,1) != "#")
                {
                    //! Match the string "EXCLUDED_JIRA_STATES"
                    if (preg_match("/\b$tomail\b/i", $line))
                    {
                        //! if the string "TO_MAIL" matched , then check for to mail id mentioned in the .cfg file.
                        //echo $line."\n";
                        $pieces = explode("=", $line);
                        //echo $pieces[1]."\n";
                        $str=$pieces[1];
                    }
                }

            }// end of while
            $str=trim($str," ");

            $mailId=explode( ',', $str );
            for ( $i=0;$i<count($mailId);$i++)
            {
                if($i !=count($mailId)-1)
                    $toMailId.=trim($mailId[$i]).", ";
                else
                    $toMailId.=trim($mailId[$i]);
            }
            //echo $s . "\n";
            // print_r($mailId);

        }// end of if
        else
        {
            echo "./config/slaConfig.cfg is not available , please create the ./config/slaConfig.cfg File and have the Jira states in this file\n";
        }

    }// end of function


    //$readStr="TO_MAIL";

    // readToMail($tomail,$readStr);

    public static function getArraOfBurtsBelongsToSFEngineer($slaDLIST,$userName)
    {
        //! Parent loop
        //! This loop will loop through the entire  double link list, while travesing , it is traversed & will keep the itm in the list itself.
        //! That is is the reason, I have kept as "IT_MODE_KEEP",  Node deletion will take place inside the loop itself, based on match logic.
        //! SplDoublyLinkedList::IT_MODE_LIFO (Stack style)
        //! SplDoublyLinkedList::IT_MODE_FIFO (Queue style) The behavior of the iterator (either one or the other)
        //! SplDoublyLinkedList::IT_MODE_DELETE (Elements are deleted by the iterator)
        //! SplDoublyLinkedList::IT_MODE_KEEP (Elements are traversed by the iterator)
        //! A doubly-linked list allows you to efficiently bypass and add large data sets without re-hashing.
        //! SplDoublyLinkedList  in php (1. SplStack, 2.SplQueue)
        //! http://web.archive.org/web/20130805120049/http://blueparabola.com/blog/spl-deserves-some-reiteration


        //! In ths inner lopp, iT tries to collect all the object belongs to one user,
        //! as loop pass through, the last element is left out, which invalidate the pointer,
        //! hence condition written to collect Object before it gets invalidated.
        $mgrBurtCount=array();
        $slaDLIST->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO);
        for ($slaDLIST->rewind(); $slaDLIST->valid(); )
        {
            if($slaDLIST->current()->assigneeKey == $userName)
            {
                $mgrBurtCount[]=$slaDLIST->current();
                $slaDLIST->offsetUnset($slaDLIST->key());
                if($slaDLIST->count()>1)
                    $slaDLIST->rewind();
            }
            else
            {
                $slaDLIST->next();
            }
        }
        $slaDLIST->rewind();
        return $mgrBurtCount;
    }
}

/*
$readStr="TO_MAIL";
$tomail='';
Util::readMailiId($tomail,$readStr);
*/
