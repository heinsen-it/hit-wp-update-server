<?php
namespace hitwpupdateserver\app\config;
class AppVersion
{
	const MAJOR = 0;
	const MINOR = 0;
	const PATCH = 1;
    const POINT = 0;

	public static function get()
	{
		$commitHash = trim(exec('git log --pretty="%h" -n1 HEAD'));

		$commitDate = new \DateTime(trim(exec('git log -n1 --pretty=%ci HEAD')));
		$commitDate->setTimezone(new \DateTimeZone('UTC'));

		return sprintf('%s.%s.%s.%s', self::MAJOR, self::MINOR, self::PATCH,self::POINT);
	}
//ABR958071108845
    public static function gethash()
    {
        $commitHash = trim(exec('git log --pretty="%h" -n1 HEAD'));

        return sprintf('%s', $commitHash);
    }


    public static function getcount()
    {
        $count = trim(exec('git rev-list --count HEAD'));

        return sprintf('%s', $count);

    }

    public static function getdate()
    {
        $commitDate = new \DateTime(trim(exec('git log -n1 --pretty=%ci HEAD')));
        $commitDate->setTimezone(new \DateTimeZone('Europe/Berlin'));

        return sprintf('%s',$commitDate->format('d.m.Y H:i')."  Uhr");
    }

    public static function GetFiles()
    {
        $commitHash = trim(exec('git log --oneline --pretty=format: --name-only | sort -u'));
     //   $commitHash = trim(exec('git log --name-only 76548cdba44cd38fe1a9c69c260175cd3da823af HEAD'));
//var_dump($commitHash);

        return sprintf('%s', $commitHash);

    }


    public static function GetCommits()
    {
        $output = array();
        exec("git log",$output);
        $history = array();
        foreach($output as $line){
            if(strpos($line, 'commit')===0){
                if(!empty($commit)){
                    array_push($history, $commit);
                    unset($commit);
                }
                $commit['hash']   = substr($line, strlen('commit'));
            }
            else if(strpos($line, 'Author')===0){
                $commit['author'] = substr($line, strlen('Author:'));
            }
            else if(strpos($line, 'Date')===0){
                $commit['date']   = substr($line, strlen('Date:'));
            }
            else{
                if(isset($commit['message']))
                    $commit['message'] .= $line;
                else
                    $commit['message'] = $line;
            }
        }

        print_r($history);
    }

}

?>