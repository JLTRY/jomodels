<?php
namespace Joomla\CMS\Uri;
define("_JEXEC", 1);
define("JPATH_ROOT", "F:\Sites\site OVH JLT local\joomla_5.0");
require_once(__DIR__ . "/../src/Helper/JOModelsHelper.php");
use JLTRY\Plugin\Content\JOModels\Helper\JOModelsHelper;
use JLTRY\Plugin\Content\JOModels\Helper\JOModel;
use JLTRY\Plugin\Content\JOModels\Helper\JOFileModel;


class Uri {
    public static function root()
    {
        return "/joomla_5.0/";
    }
}



function test() {
    JOModelsHelper::init();
    $allmodels = [
        new JOModel("warning",
        '<pre><div class="%{class|blubox-jck}" style="padding-left:0px!important">
<table  cellspacing="0" cellpadding="0" style="background: transparent; padding-top:1em;padding-left:1em;">
    <tr>
        <td nowrap="nowrap" valign="top" border="0">
            <span style="position: relative; top: -2px;margin-right:5px;margin-left:15px;">
                {model:img src=%{ROOTURI}//plugins/content/template/Attention_niels_epting.svg}
            </span> 
        </td>
        <td valign="top" style="padding-right: 5px;padding-left:10px;" border="0">
            %{content}
        </td> 
    </tr>
</table>
</div></pre>',
""),
    new JOModel("githublink", 
    '<pre>&nbsp;<a href="%{link|/}" title="%{text|%{link}}"><img src="%{ROOTURI}/images/github.webp" width="%{width|20}">%{text|%{link}}</a></pre>',
    JOModel::_PRIO1),
    new JOModel("weblink", 
    '<pre>&nbsp;<a href="%{link|/}" title="%{text|%{link}}"><img src="%{ROOTURI}//images/web_link.png" width="%{width|20}" />%{text|%{link}}</a></pre>',
    JOModel::_PRIO1)
   ];
    foreach (glob( JPATH_ROOT . '/files/jocodes/' . '*.tmpl') as $file)
	{
		$splitar = preg_split("/\./", basename($file));
		$allmodels[] = new JOFileModel($splitar[0], $file);
	}
    $alltext=
    [//"<p>The release is available on github.{model:githublink |link=https://github.com/jmcameron/attachments|text=attachments}</p>",
   /*  '{model:warning}<p class="web-jck">
    {model:weblink link=http://alexgorbatchev.com/SyntaxHighlighter/|text=SyntaxHighlighter/}
<p>&nbsp;Le site n\'existe plus. Le plugin n\'utilise que du javascript, contrairement à la famile <b>geshi</b> qui elle utilise du php.</p>
<p>Le site ne fonctionne pas très bien.</p>{/model:warning}', */
'<p>{model:warning}Attention à bien placer xml:lang juste après la balise pre!!!{/model:warning}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<h3>Site github</h3>
<p>J\'avais fait un "<strong>fork</strong>" du repo principal non mis à jour depuis 12 ans !!!</p>
<p>{model:githublink link=https://github.com/JLTRY/Joomla-GeSHi}</p>
<p>J\'ai décidé de créer un nouveau plugin hébergé aussi sur github</p>
<p>{model:githublink link=https://github.com/JLTRY/jocodehighlight}</p>
<p>&nbsp;</p>
<p>&nbsp;<span class="hide_attachments_token">{attachments}</span></p>'];
    foreach ($alltext as $text) {
        JOModelsHelper::replaceModels($text, $allmodels);
        echo $text;
    }
    
}
?>
<html>
<link href="/joomla_5.0/templates/bootstrap4/css/jcktypography/typography.min.css?cc1c3c" rel="stylesheet">
<?php test();?>
</html> 

