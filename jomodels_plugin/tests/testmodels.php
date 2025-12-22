<?php
namespace Joomla\CMS\Uri;
define("_JEXEC", 1);
define("JPATH_ROOT", "F:\Sites\site OVH JLT local\joomla_5.0");
require_once(__DIR__ . "/../src/Helper/JOModelsHelper.php");
require_once(__DIR__ . "/Utility.php");
require_once(__DIR__ . "/Factory.php");
require_once(__DIR__ . "/FieldsHelper.php");
require_once(__DIR__ . "/Log.php");
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
        "warning" => new JOModel("warning",
        '<pre><div class="%{class|blubox-jck}" style="padding-left:0px!important">
<table  cellspacing="0" cellpadding="0" style="background: transparent; padding-top:1em;padding-left:1em;">
    <tr>
        <td nowrap="nowrap" valign="top" border="0">
            <span style="position: relative; top: -2px;margin-right:5px;margin-left:15px;">
                {jomodel:img src="%{ROOTURI}/plugins/content/template/Attention_niels_epting.svg"}
            </span> 
        </td>
        <td valign="top" style="padding-right: 5px;padding-left:10px;" border="0">
            %{content}
        </td> 
    </tr>
</table>
</div></pre>',
COM_JOMODELS_FULL),
         "info" => new JOModel("info",
'<pre>
	<div class="%{class|blubox-jck}">
		<table style="background: transparent; padding-top: 1em; padding-left: 1em;">
			<tbody>
				<tr>
					<td border="0">
						<span style="position: relative; top: -2px; margin-right: 5px; margin-left: 15px;">
							{model:img src=/images/Information.png|width=36}
						</span>
					</td>
					<td style="padding-right: 5px; padding-left: 20px;" border="0" valign="top">
						%{content}
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</pre>',
COM_JOMODELS_FULL),
    "joomlalink" => new JOModel("joomlalink", 
    '<pre>&nbsp;<a href="https://extensions.joomla.org/extension/%{link|/}" title="%{text|%{link}}"><img src="https://extensions.joomla.org/templates/joomla/favicon.ico" width="%{width|20}">%{text|%{link}}</a></pre>',
    COM_JOMODELS_NORMAL),
    "githublink" => new JOModel("githublink", 
    '<pre>&nbsp;<a href="https://github.com/%{link|/}" title="%{text|%{link}}"><img src="%{ROOTURI}/images/github.webp" width="%{width|20}" />%{text|%{link}}</a></pre>',
    COM_JOMODELS_NORMAL),
    "weblink" => new JOModel("weblink", 
    '<pre>&nbsp;<a href="%{link|/}" title="%{text|%{link}}"><img src="%{ROOTURI}/images/web_link.png" width="%{width|20}" />%{text|%{link}}</a></pre>',
    COM_JOMODELS_NORMAL),
     "jdate" => new JOModel("jdate", 
    '<pre><span class="icon-calendar icon-fw" style="text-align: left; min-width: 11ch; font-size: 11px !important; margin-left: -2px;" aria-hidden="true">&nbsp;%{date}</span></pre>',
     COM_JOMODELS_NORMAL),
     "readmore" => new JOModel("readmore", 
     '<pre><a class="btn btn-secondary" href="%{url}" itemprop="url">     <span class="fa fa-chevron-right" style="text-align: left;"></span><span style="text-align: left;">Lire la suite... &nbsp;%{text} </span></a></pre>',
     COM_JOMODELS_NORMAL),
     "readmorewiki" => new JOModel("readmorewiki", 
     '<pre>{jomodel:readmore url="http://wiki.jltryoen.fr/%{article}" text="%{text|%{article}}"}</pre>',
     COM_JOMODELS_NORMAL),
     "box-jck" => new JOModel("box-jck", 
     '<pre>
<div class="%{class|blubox-jck}">%{title|x}%{content|contenu}</div>
</pre>',
     COM_JOMODELS_FULL),
     "grebox-jck" => new JOModel("grebox-jck", 
     '<pre>{model:box-jck title=%{title|&nbsp;}|class=%{class|grebox-jck}}
  %{content|contenu}
  {/model:box-jck}
</pre>',
     COM_JOMODELS_FULL),
     "intralink" =>
      new JOModel("intralink", 
     '<pre>&nbsp;<a href="%{link|/}" title="%{text|%{link}}"> 
    <img src="/images/favicon.ico" width="%{width|20}" />%{text|%{link}}</a></pre>',
    COM_JOMODELS_NORMAL),
     "bracket" =>
      new JOModel("bracket", 
     '&#123;',
    COM_JOMODELS_NORMAL),
    "userimagelink" => new JOModel("userimagelink",
    '<a href="%{u:photos}">%{ud:image-photos} %{u:titre-photos}</a>',
    COM_JOMODELS_NORMAL)
   ];
   
    foreach (glob( JPATH_ROOT . '/files/jomodels/' . '*.tmpl') as $file)
    {
        $splitar = preg_split("/\./", basename($file));
        $allmodels[$splitar[0]] = new JOFileModel($splitar[0], $file);
    }
    $alltext=
    [/*"<p>The release is available on github.{model:githublink |link=https://github.com/jmcameron/attachments|text=attachments}</p>",
       '{model:warning}<p class="web-jck">
    {model:weblink link=http://alexgorbatchev.com/SyntaxHighlighter/|text=SyntaxHighlighter/}
<p>&nbsp;Le site n\'existe plus. Le plugin n\'utilise que du javascript, contrairement à la famile <b>geshi</b> qui elle utilise du php.</p>
<p>Le site ne fonctionne pas très bien.</p>{/model:warning}',   
'<p>{jomodel:warning}Attention à bien placer xml:lang juste après la balise pre!!!{/jomodel:warning}</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<h3>Site github</h3>
<p>J\'avais fait un "<strong>fork</strong>" du repo principal non mis à jour depuis 12 ans !!!</p>
<p>{jomodel:githublink link="https://github.com/JLTRY/Joomla-GeSHi"}</p>
<p>J\'ai décidé de créer un nouveau plugin hébergé aussi sur github</p>
<p>{jomodel:githublink link="https://github.com/JLTRY/jocodehighlight"}</p>
<p>&nbsp;</p>
<p>&nbsp;<span class="hide_attachments_token">{attachments}</span></p>',

'<p>{jomodel:info}{jomodel:jdate date="1/10/2025"}&nbsp;<br> LE souci a l\'air d\'être corrigé avec une version de <strong>Joomla &gt;= 5.4 beta3</strong>{/jomodel:info}</p>
<p>&nbsp;</p>
<p>Sur un site hébergé par <strong>OVH</strong> et si on active le cache File j\'ai cette erreur d\'affichée:</p>',
'<p>{model:info}<strong>Warning</strong>: Zend OPcache API is restricted by "restrict_api" configuration directive{/model:info}</p>' ,
'{model:readmorewiki article=Docker|text=Docker}{/model:readmorewiki}',
'{model:intralink link=http://wiki.jltryoen.fr/index.php?title=Windows_10/Bash|text=Windows_10/Bash}',
 '{model:extension name=JO\'s Favorites|link=site-management/site-links/jo-s-favorites/|githublink=https://github.com/JLTRY/jofavorites}',
  '{model:grebox-jck title=Title} test {/model:grebox-jck}',
  '{jomodel:grebox-jck title="Title gtr"} test {/jomodel:grebox-jck}',
  '{jomodel:img src="%{ROOTURI}/plugins/content/template/Attention_niels_epting.svg"}',
  '{model:intralink link=http://wiki.jltryoen.fr/index.php?title=Windows_10/Bash|text=Windows_10/Bash}',
  '{model:weblink link=http://alexgorbatchev.com/SyntaxHighlighter/|text=SyntaxHighlighter/}',
  
  '{model:warning}<p class="web-jck">
    {model:weblink link=http://alexgorbatchev.com/SyntaxHighlighter/|text=SyntaxHighlighter/}
<p>&nbsp;Le site n\'existe plus. Le plugin n\'utilise que du javascript, contrairement à la famile <b>geshi</b> qui elle utilise du php.</p>
<p>Le site ne fonctionne pas très bien.</p>{/model:warning}'*/
//'{jomodel:readmorewiki article="Docker" text="Docker"}',
//'{jomodel:githublink link="jmcameron/attachments" text="attachments"}',
//'{jomodel:githublink link=jmcameron/attachments|text=attachments}'
//    '<p>{model:warning}Attention à bien placer xml:lang juste après la balise pre!!!{/model:warning}</p>',
//    '{model:bracket}',
    '{model:userimagelink}'
];
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

