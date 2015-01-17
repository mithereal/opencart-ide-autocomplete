<?php
    require 'config.php';
    
      function getModels( $basedir = DIR_APPLICATION ) {
        $permission = array();
        $files      = glob( $basedir . 'model/*/*.php' );
        foreach ( $files as $file ) {
            $data  = explode( '/', dirname( $file ) );
            $names = explode( '_', basename( $file, '.php' ) );
            if ( !$names ) {
                $names = array( basename( $file, '.php' ) );
            }
            $permission[] = 'Model' . ucfirst( end( $data ) ) . implode( '', array_map( function ( $x ) {
                    return ucfirst( $x );
                }, $names ) ) . ' $model_' . end( $data ) . '_' . basename( $file, '.php' );

        }
        return $permission;
    }

    function getClasses( $file ) {
        $result  = array();
        $pattern = '%library/([a-z]+)\.php%';
        $content = file_get_contents( $file );
        if ( preg_match_all( $pattern, $content, $matches ) ) {
            foreach ( $matches[1] as $item ) {
                if( $item == 'template' ){
                    continue; //remove template - var of Controller
                }
                $result[] = sprintf( '%s $%s', ucfirst( $item ), $item );
            }
        }
        return $result;
    }

    function getLineOfFile( $fp, $needle ) {
        rewind( $fp );

        $lineNumber = 0;

        while ( !feof( $fp ) ) {
            $line = fgets( $fp );
            if ( !( strpos( $line, $needle ) === false ) ) {
                break;
            }
            $lineNumber++;
        }

        return feof( $fp ) ? null : $lineNumber;
    }


    $rewriteController = false;
    $pathToController  = DIR_SYSTEM . 'engine/controller.php';
    $searchLine        = 'abstract class Controller {';
    $catalogPath       = 'catalog/';
    $adminPath         = 'admin/';

    $properties = array(
          'string $id'
        , 'string $template'
        , 'array $children'
        , 'array $data'
        , 'string $output'
        , 'Loader $load'
    );
    
 $html ='<html><head><script type="text/javascript" src="catalog/view/javascript/jquery/jquery-2.1.1.min.js"></script>
</head><body>';
 
    if (is_writable($pathToController)){
        $rewriteController = true;
    }

    $catalogModels   = getModels();
    $adminModels     = getModels( str_ireplace( $catalogPath, $adminPath, DIR_APPLICATION ) );
    $startupClasses  = getClasses( DIR_SYSTEM . 'startup.php' );
    $registryClasses = getClasses( 'index.php' );
    $textToInsert    = array_unique( array_merge( $properties, $startupClasses, $registryClasses, $catalogModels, $adminModels ) );

    if( $rewriteController ){
        //get line number where start Controller description
        $fp     = fopen( $pathToController, 'r' );
        $lineNumber = getLineOfFile( $fp, $searchLine );
        fclose( $fp );

        //regenerate Controller text with properties
        $file = new SplFileObject( $pathToController );
        $file->seek( $lineNumber );
        $tempFile = sprintf( "<?php %s \t/**%s", PHP_EOL, PHP_EOL );
        foreach ( $textToInsert as $val ) {
            $tempFile .= sprintf( "\t* @property %s%s", $val, PHP_EOL );
        }
        $tempFile .= sprintf( "\t**/%s%s%s", PHP_EOL, $searchLine, PHP_EOL );
        while ( !$file->eof() ) {
            $tempFile .= $file->fgets();
        }

        //write Controller
        $fp = fopen( $pathToController, 'w' );
        fwrite( $fp, $tempFile );
        fclose( $fp );
       
        $html.= '<h3>Аutocomplete Properties Successfully Installed.</h3>';
    } else {
        $html.=  '<h3>Place the following code above abstract class Controller in your system/engine/controller.php file</h3><hr>';
       	
        $properties='/**'."\n";
        
        foreach($textToInsert as $val)
        {
            $properties .= '* @property '.$val."\n";
        }
        
        $properties .= '**/'."\n";
        
        $propnum=count($textToInsert);
        
        $html.=  '<textarea rows="'. $propnum .'" cols="200" id= "code">'."\n";
        $html.= $properties;
        $html.=  "</textarea>";
        $html.=  '<hr>';
    }
 $html.=  '<script language="javascript" type="text/javascript">
$(document).ready(function () {
$("#code").focus(function() {
    var $this = $(this);
    $this.select();

    // Work around Chromes little problem
    $this.mouseup(function() {
        // Prevent further mouseup intervention
        $this.unbind("mouseup");
        return false;
    });
});
});
</script>';	    
$html.= "</body></html>";
echo $html;

