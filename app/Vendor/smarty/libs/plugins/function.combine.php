<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty combine function plugin
 *
 * Type:     function<br>
 * Name:     combine<br>
 * Date:     September 5, 2015
 * Purpose:  Combine content from several js or css files into one
 * Input:    string to count
 * Example:  {count input=$array_of_files_to_combine output=$path_to_output_file age=$seconds_to_try_recombine_file}
 *
 * @author Gorochov Ivan <dead23angel at gmail dot com>
 * @version 1.0
 * @param array
 * @param string
 * @param int
 * @return string
 */

function smarty_function_combine($params, &$smarty)
{
    require_once dirname(__FILE__) . '/minify/JSmin.php';
    require_once dirname(__FILE__) . '/minify/CSSmin.php';
    
    /**
     * Print filename
     *
     * @param string $params
     */
    if (! function_exists('smarty_print_out')) {
        function smarty_print_out($params)
        {
            $last_mtime = 0;

            if (file_exists(JS.$params['cache_file_name'])) {
                $last_mtime = file_get_contents(JS.$params['cache_file_name']);
            }
          
            $output_filename = preg_replace("/\.(js|css)$/i", "_" . $last_mtime . "." ."$1", $params['output']);
            
            if ($params['type'] == 'js') {
                echo '<script type="text/javascript" src="/js/'.$output_filename.'" charset="utf-8"></script>';
            } elseif ($params['type'] == 'css') {
                echo '<link type="text/css" rel="stylesheet" href="/js/'.$output_filename.'" />';
            } else {
                echo $output_filename;
            }
        }
    }

    /**
     * Build combined file
     *
     * @param array $params
     */
    if (! function_exists('smarty_build_combine')) {
        function smarty_build_combine($params)
        {
            $filelist = array();
            $lastest_mtime = 0;

            foreach ($params['input'] as $item) {
				if ($params['type'] == 'js') {
					$strFilePath = JS.$item;
				} elseif ($params['type'] == 'css') {
					$strFilePath = CSS.$item;
				}
                if (file_exists($strFilePath)) {
                    $mtime = filemtime($strFilePath);
                    $lastest_mtime = max($lastest_mtime, $mtime);
                    $filelist[] = array('name' => $item, 'time' => $mtime);
                } else {
                    trigger_error('File '.$strFilePath.' does not exists!', E_USER_WARNING);
                }
            }
            
            if ($params['debug'] == true) {
                $output_filename = '';
                foreach ($filelist as $file) {
                    if ($params['type'] == 'js') {
                        $output_filename .= '<script type="text/javascript" src="//'.$_SERVER['SERVER_NAME']. '/js/' . $file['name'].'" charset="utf-8"></script>';
                    } elseif ($params['type'] == 'css') {
                        $output_filename .= '<link type="text/css" rel="stylesheet" href="//'.$_SERVER['SERVER_NAME']. '/css/' .$file['name'].'" />';
                    }
                }
                
                echo $output_filename;
                return;
            }

            $last_cmtime = 0;

            if (file_exists(JS.$params['cache_file_name'])) {
                $last_cmtime = file_get_contents(JS.$params['cache_file_name']);
            }

            if ($lastest_mtime > $last_cmtime) {
                $glob_mask = preg_replace("/\.(js|css)$/i", "_*.$1", $params['output']);
                $files_to_cleanup = glob(JS.$glob_mask);

                foreach ($files_to_cleanup as $cfile) {
                    if (is_file(JS.$cfile) && file_exists(JS.$cfile)) {
                        @unlink(JS.$cfile);
                    }
                }

                $output_filename = preg_replace("/\.(js|css)$/i", "_" . $lastest_mtime . "." ."$1", $params['output']);
                $fh = fopen(JS.$output_filename, "a+");

                if (flock($fh, LOCK_EX)) {
                    foreach ($filelist as $file) {
                        $min = '';
                        
                        if ($params['type'] == 'js') {
                            $min = JSMin::minify(file_get_contents(JS.$file['name']));
                        } elseif ($params['type'] == 'css') {
                            $min = CSSMin::minify(file_get_contents(CSS.$file['name']));
                        } else {
                            fputs($fh, PHP_EOL.PHP_EOL."/* ".$file['name']." @ ".date("c", $file['time'])." */".PHP_EOL.PHP_EOL);
                            $min = file_get_contents(JS.$file['name']);
                        }
                        
                        fputs($fh, $min);
                    }

                    flock($fh, LOCK_UN);
                    file_put_contents(JS.$params['cache_file_name'], $lastest_mtime, LOCK_EX);
                }

                fclose($fh);
                clearstatcache();
            }

            touch(JS.$params['cache_file_name']);
            smarty_print_out($params);
        }
    }


    if (isset($params['input'])) {
        if (is_array($params['input']) && count($params['input']) > 0) {
            $ext = pathinfo($params['input'][0], PATHINFO_EXTENSION);
            if (in_array($ext, array('js', 'css'))) {
                $params['type'] = $ext;
                if (!isset($params['output'])) {
                    $params['output'] = dirname($params['input'][0]).'/combined.'.$ext;
                }
                if (!isset($params['age'])) {
                    $params['age'] = 2628000; //1 month
                }
                if (!isset($params['cache_file_name'])) {
                    $params['cache_file_name'] = $params['output'].'.cache';
                }
                
                if( $_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1' ) {
                	$params['debug'] = true;
                } else if (!isset($params['debug'])) {
                    $params['debug'] = false;
                }
                
                $cache_file_name = $params['cache_file_name'];
                
                if ($params['debug'] == true) {
                    smarty_build_combine($params);
                    return;
                }

                if (file_exists(JS.$cache_file_name)) {
                    $cache_mtime = filemtime(JS.$cache_file_name);
                    if ($cache_mtime+$params['age'] < time()) {
                        smarty_build_combine($params);
                    } else { 
                        smarty_print_out($params);
                    }
                } else {
                    smarty_build_combine($params);
                }
            } else {
                trigger_error("input file must have js or css extension", E_USER_NOTICE);
                return;
            }
        } else {
            trigger_error("input must be array and have one item at least", E_USER_NOTICE);
            return;
        }
    } else {
        trigger_error("input cannot be empty", E_USER_NOTICE);
        return;
    }
}
