<?php
require 'config.php';
$properties=array();

$properties[]='string $id';
$properties[]='string $template';
$properties[]='array $children';
$properties[]='array $data';
$properties[]='string $output';
$properties[]='Loader $load';
$properties[]='Config $config';
$properties[]='DB $db';
$properties[]='Log $log';
$properties[]='Request $request';
$properties[]='Response $response';
$properties[]='Cache $cache';
$properties[]='Url $url';
$properties[]='Document $document';
$properties[]='Language $language';
$properties[]='Customer $customer';
$properties[]='Currency $currency';
$properties[]='Tax $tax';
$properties[]='Weight $weight';
$properties[]='Measurement $measurement';
$properties[]='Cart $cart';
$properties[]='ModelToolSeoUrl $model_seo_url';



function getModels($basedir=DIR_APPLICATION) {
$permission =array();
        $files = glob($basedir. 'model/*/*.php');
        foreach ($files as $file) {
            $data = explode('/', dirname($file));
            $permission[] = 'Model' . ucfirst(end($data)) . ucfirst(basename($file, '.php')) . ' $model_' . end($data) . '_' . basename($file, '.php');
            
        }
        return $permission;
    }
    
$adminurl=str_ireplace('catalog/','admin/',DIR_APPLICATION);
$catalog_models=getModels();
$admin_models=getModels($adminurl);
$textToInsert=array_merge($properties,$catalog_models);
$textToInsert=array_merge($textToInsert,$admin_models);
$textToInsert=array_unique($textToInsert);

echo '<h3>Place the following code above abstract class Controller in your system/engine/controller.php file</h3><hr>';
echo '/**','<br>';
foreach($textToInsert as $val)
{
	
	echo '* @property '.$val.'<br>';
}
echo '**/','<br>';
echo '<hr>';
?>
