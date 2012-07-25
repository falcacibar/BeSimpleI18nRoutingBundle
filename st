<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="robots" content="noindex,nofollow" />
        <title>    Notice: Undefined index: recomendacion in /private/var/www/loogares/src/Loogares/LugarBundle/Controller/AjaxController.php line 445 (500 Internal Server Error)
</title>
        <link href="/loogares/web/bundles/framework/css/exception_layout.css" rel="stylesheet" type="text/css" media="all" />
        <link href="/loogares/web/bundles/framework/css/exception.css" rel="stylesheet" type="text/css" media="all" />
    </head>
    <body>
        <div id="content">
            <div class="header clear_fix">
                <div class="header_logo">
                    <img src="/loogares/web/bundles/framework/images/logo_symfony.gif" alt="Symfony" />
                </div>

                <div class="search">
                  <form method="get" action="http://symfony.com/search">
                    <div class="form_row">

                      <label for="search_id">
                          <img src="/loogares/web/bundles/framework/images/grey_magnifier.png" alt="Search on Symfony website" />
                      </label>

                      <input name="q" id="search_id" type="search" placeholder="Search on Symfony website" />

                      <button type="submit">
                        <span class="border_l">
                          <span class="border_r">
                            <span class="btn_bg">OK</span>
                          </span>
                        </span>
                      </button>
                    </div>
                   </form>
                </div>
            </div>

                <div class="sf-exceptionreset">

    <div class="block_exception">
        <div class="block_exception_detected clear_fix">
            <div class="illustration_exception">
                <img alt="Exception detected!" src="/loogares/web/bundles/framework/images/exception_detected.gif"/>
            </div>
            <div class="text_exception">

                <div class="open_quote">
                    <img alt="" src="/loogares/web/bundles/framework/images/open_quote.gif"/>
                </div>

                <h1>
                    Notice: Undefined index: recomendacion in /private/var/www/loogares/src/Loogares/LugarBundle/Controller/AjaxController.php line 445
                </h1>

                <div>
                    <strong>500</strong> Internal Server Error - <abbr title="ErrorException">ErrorException</abbr>
                </div>

                                
                <div class="close_quote">
                    <img alt="" src="/loogares/web/bundles/framework/images/close_quote.gif"/>
                </div>

            </div>
        </div>
    </div>

            <div class="block">
            <h2>Stack Trace</h2>
    
    <a id="traces_link_0"></a>
    <ol class="traces list_exception" id="traces_0" style="display: block">
                    <li>
                
    
    in /private/var/www/loogares/vendor/symfony/src/Symfony/Component/HttpKernel/Debug/ErrorHandler.php at line 65&nbsp;
    <a href="#" onclick="toggle('trace_0_0'); switchIcons('icon_0_0_open', 'icon_0_0_close'); return false;"><img class="toggle" id="icon_0_0_close" alt="-" src="/loogares/web/bundles/framework/images/blue_picto_less.gif" style="visibility: display" /><img class="toggle" id="icon_0_0_open" alt="+" src="/loogares/web/bundles/framework/images/blue_picto_more.gif" style="visibility: hidden; margin-left: -18px" /></a>    <div id="trace_0_0" style="display: block" class="trace">
        <ol start="62"><li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</code></li>
<li><code></code></li>
<li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if&nbsp;(<span style="color: #0000BB">error_reporting</span><span style="color: #007700">()&nbsp;&amp;&nbsp;</span><span style="color: #0000BB">$level&nbsp;</span><span style="color: #007700">&amp;&amp;&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">level&nbsp;</span><span style="color: #007700">&amp;&nbsp;</span><span style="color: #0000BB">$level</span><span style="color: #007700">)&nbsp;{</span></code></li>
<li class="selected"><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;throw&nbsp;new&nbsp;\<span style="color: #0000BB">ErrorException</span><span style="color: #007700">(</span><span style="color: #0000BB">sprintf</span><span style="color: #007700">(</span><span style="color: #DD0000">'%s:&nbsp;%s&nbsp;in&nbsp;%s&nbsp;line&nbsp;%d'</span><span style="color: #007700">,&nbsp;isset(</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">levels</span><span style="color: #007700">[</span><span style="color: #0000BB">$level</span><span style="color: #007700">])&nbsp;?&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">levels</span><span style="color: #007700">[</span><span style="color: #0000BB">$level</span><span style="color: #007700">]&nbsp;:&nbsp;</span><span style="color: #0000BB">$level</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$message</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$file</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$line</span><span style="color: #007700">));</span></code></li>
<li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</code></li>
<li><code></code></li>
<li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;<span style="color: #0000BB">false</span><span style="color: #007700">;</span></code></li></ol>
    </div>
            </li>
                    <li>
                    at
    <strong>
        <abbr title="Symfony\Component\HttpKernel\Debug\ErrorHandler">ErrorHandler</abbr>
        -&gt;handle
    </strong>
    ('8', 'Undefined index: recomendacion', '/private/var/www/loogares/src/Loogares/LugarBundle/Controller/AjaxController.php', '445', <em>array</em>('em' => <em>object</em>(<abbr title="Doctrine\ORM\EntityManager">EntityManager</abbr>), 'rr' => <em>object</em>(<abbr title="Loogares\UsuarioBundle\Repository\RecomendacionRepository">RecomendacionRepository</abbr>), 'usuario' => 'anon.'))

    <br />
    in /private/var/www/loogares/src/Loogares/LugarBundle/Controller/AjaxController.php at line 445&nbsp;
    <a href="#" onclick="toggle('trace_0_1'); switchIcons('icon_0_1_open', 'icon_0_1_close'); return false;"><img class="toggle" id="icon_0_1_close" alt="-" src="/loogares/web/bundles/framework/images/blue_picto_less.gif" style="visibility: hidden" /><img class="toggle" id="icon_0_1_open" alt="+" src="/loogares/web/bundles/framework/images/blue_picto_more.gif" style="visibility: display; margin-left: -18px" /></a>    <div id="trace_0_1" style="display: none" class="trace">
        <ol start="442"><li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #0000BB">$rr&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$em</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">getRepository</span><span style="color: #007700">(</span><span style="color: #DD0000">"LoogaresUsuarioBundle:Recomendacion"</span><span style="color: #007700">);</span></code></li>
<li><code></code></li>
<li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #0000BB">$usuario&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">get</span><span style="color: #007700">(</span><span style="color: #DD0000">'security.context'</span><span style="color: #007700">)-&gt;</span><span style="color: #0000BB">getToken</span><span style="color: #007700">()-&gt;</span><span style="color: #0000BB">getUser</span><span style="color: #007700">();</span></code></li>
<li class="selected"><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #0000BB">$recomendacion&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$rr</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">find</span><span style="color: #007700">(</span><span style="color: #0000BB">$_POST</span><span style="color: #007700">[</span><span style="color: #DD0000">'recomendacion'</span><span style="color: #007700">]);</span></code></li>
<li><code></code></li>
<li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #FF8000">//&nbsp;Se&nbsp;envía&nbsp;mail&nbsp;al&nbsp;usuario&nbsp;que&nbsp;recomendó&nbsp;informándole&nbsp;del&nbsp;útil</span></code></li>
<li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #0000BB">$mail&nbsp;</span><span style="color: #007700">=&nbsp;array();</span></code></li></ol>
    </div>
            </li>
                    <li>
                    at
    <strong>
        <abbr title="Loogares\LugarBundle\Controller\AjaxController">AjaxController</abbr>
        -&gt;utilMailAction
    </strong>
    ()

            </li>
                    <li>
                    at
    <strong>
        <abbr title=""></abbr>
        call_user_func_array
    </strong>
    (<em>array</em>(<em>object</em>(<abbr title="Loogares\LugarBundle\Controller\AjaxController">AjaxController</abbr>), 'utilMailAction'), <em>array</em>())

    <br />
    in <abbr title="/private/var/www/loogares/app/">kernel.root_dir</abbr>/cache/dev/classes.php at line 3905&nbsp;
    <a href="#" onclick="toggle('trace_0_3'); switchIcons('icon_0_3_open', 'icon_0_3_close'); return false;"><img class="toggle" id="icon_0_3_close" alt="-" src="/loogares/web/bundles/framework/images/blue_picto_less.gif" style="visibility: hidden" /><img class="toggle" id="icon_0_3_open" alt="+" src="/loogares/web/bundles/framework/images/blue_picto_more.gif" style="visibility: display; margin-left: -18px" /></a>    <div id="trace_0_3" style="display: none" class="trace">
        <ol start="3902"><li><code></code></li>
<li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #0000BB">$arguments&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">resolver</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">getArguments</span><span style="color: #007700">(</span><span style="color: #0000BB">$request</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$controller</span><span style="color: #007700">);</span></code></li>
<li><code></code></li>
<li class="selected"><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #0000BB">$response&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">call_user_func_array</span><span style="color: #007700">(</span><span style="color: #0000BB">$controller</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$arguments</span><span style="color: #007700">);</span></code></li>
<li><code></code></li>
<li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if&nbsp;(!<span style="color: #0000BB">$response&nbsp;</span><span style="color: #007700">instanceof&nbsp;</span><span style="color: #0000BB">Response</span><span style="color: #007700">)&nbsp;{</span></code></li>
<li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #0000BB">$event&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">GetResponseForControllerResultEvent</span><span style="color: #007700">(</span><span style="color: #0000BB">$this</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$request</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$type</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$response</span><span style="color: #007700">);</span></code></li></ol>
    </div>
            </li>
                    <li>
                    at
    <strong>
        <abbr title="Symfony\Component\HttpKernel\HttpKernel">HttpKernel</abbr>
        -&gt;handleRaw
    </strong>
    (<em>object</em>(<abbr title="Symfony\Component\HttpFoundation\Request">Request</abbr>), '1')

    <br />
    in <abbr title="/private/var/www/loogares/app/">kernel.root_dir</abbr>/cache/dev/classes.php at line 3875&nbsp;
    <a href="#" onclick="toggle('trace_0_4'); switchIcons('icon_0_4_open', 'icon_0_4_close'); return false;"><img class="toggle" id="icon_0_4_close" alt="-" src="/loogares/web/bundles/framework/images/blue_picto_less.gif" style="visibility: hidden" /><img class="toggle" id="icon_0_4_open" alt="+" src="/loogares/web/bundles/framework/images/blue_picto_more.gif" style="visibility: display; margin-left: -18px" /></a>    <div id="trace_0_4" style="display: none" class="trace">
        <ol start="3872"><li><code>&nbsp;&nbsp;&nbsp;&nbsp;public&nbsp;function&nbsp;<span style="color: #0000BB">handle</span><span style="color: #007700">(</span><span style="color: #0000BB">Request&nbsp;$request</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$type&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">HttpKernelInterface</span><span style="color: #007700">::</span><span style="color: #0000BB">MASTER_REQUEST</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$catch&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">true</span><span style="color: #007700">)</span></code></li>
<li><code>&nbsp;&nbsp;&nbsp;&nbsp;{</code></li>
<li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;try&nbsp;{</code></li>
<li class="selected"><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;<span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">handleRaw</span><span style="color: #007700">(</span><span style="color: #0000BB">$request</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$type</span><span style="color: #007700">);</span></code></li>
<li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}&nbsp;catch&nbsp;(\<span style="color: #0000BB">Exception&nbsp;$e</span><span style="color: #007700">)&nbsp;{</span></code></li>
<li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;if&nbsp;(<span style="color: #0000BB">false&nbsp;</span><span style="color: #007700">===&nbsp;</span><span style="color: #0000BB">$catch</span><span style="color: #007700">)&nbsp;{</span></code></li>
<li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;throw&nbsp;<span style="color: #0000BB">$e</span><span style="color: #007700">;</span></code></li></ol>
    </div>
            </li>
                    <li>
                    at
    <strong>
        <abbr title="Symfony\Component\HttpKernel\HttpKernel">HttpKernel</abbr>
        -&gt;handle
    </strong>
    (<em>object</em>(<abbr title="Symfony\Component\HttpFoundation\Request">Request</abbr>), '1', <em>true</em>)

    <br />
    in <abbr title="/private/var/www/loogares/app/">kernel.root_dir</abbr>/cache/dev/classes.php at line 4879&nbsp;
    <a href="#" onclick="toggle('trace_0_5'); switchIcons('icon_0_5_open', 'icon_0_5_close'); return false;"><img class="toggle" id="icon_0_5_close" alt="-" src="/loogares/web/bundles/framework/images/blue_picto_less.gif" style="visibility: hidden" /><img class="toggle" id="icon_0_5_open" alt="+" src="/loogares/web/bundles/framework/images/blue_picto_more.gif" style="visibility: display; margin-left: -18px" /></a>    <div id="trace_0_5" style="display: none" class="trace">
        <ol start="4876"><li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">container</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">set</span><span style="color: #007700">(</span><span style="color: #DD0000">'request'</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$request</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'request'</span><span style="color: #007700">);</span></code></li>
<li><code></code></li>
<li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;try&nbsp;{</code></li>
<li class="selected"><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #0000BB">$response&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">parent</span><span style="color: #007700">::</span><span style="color: #0000BB">handle</span><span style="color: #007700">(</span><span style="color: #0000BB">$request</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$type</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$catch</span><span style="color: #007700">);</span></code></li>
<li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}&nbsp;catch&nbsp;(\<span style="color: #0000BB">Exception&nbsp;$e</span><span style="color: #007700">)&nbsp;{</span></code></li>
<li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">container</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">leaveScope</span><span style="color: #007700">(</span><span style="color: #DD0000">'request'</span><span style="color: #007700">);</span></code></li>
<li><code></code></li></ol>
    </div>
            </li>
                    <li>
                    at
    <strong>
        <abbr title="Symfony\Bundle\FrameworkBundle\HttpKernel">HttpKernel</abbr>
        -&gt;handle
    </strong>
    (<em>object</em>(<abbr title="Symfony\Component\HttpFoundation\Request">Request</abbr>), '1', <em>true</em>)

    <br />
    in <abbr title="/private/var/www/loogares/app/">kernel.root_dir</abbr>/bootstrap.php.cache at line 547&nbsp;
    <a href="#" onclick="toggle('trace_0_6'); switchIcons('icon_0_6_open', 'icon_0_6_close'); return false;"><img class="toggle" id="icon_0_6_close" alt="-" src="/loogares/web/bundles/framework/images/blue_picto_less.gif" style="visibility: hidden" /><img class="toggle" id="icon_0_6_open" alt="+" src="/loogares/web/bundles/framework/images/blue_picto_more.gif" style="visibility: display; margin-left: -18px" /></a>    <div id="trace_0_6" style="display: none" class="trace">
        <ol start="544"><li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">boot</span><span style="color: #007700">();</span></code></li>
<li><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}</code></li>
<li><code></code></li>
<li class="selected"><code>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;<span style="color: #0000BB">$this</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">getHttpKernel</span><span style="color: #007700">()-&gt;</span><span style="color: #0000BB">handle</span><span style="color: #007700">(</span><span style="color: #0000BB">$request</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$type</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$catch</span><span style="color: #007700">);</span></code></li>
<li><code>&nbsp;&nbsp;&nbsp;&nbsp;}</code></li>
<li><code></code></li>
<li><code>&nbsp;&nbsp;&nbsp;&nbsp;</code></li></ol>
    </div>
            </li>
                    <li>
                    at
    <strong>
        <abbr title="Symfony\Component\HttpKernel\Kernel">Kernel</abbr>
        -&gt;handle
    </strong>
    (<em>object</em>(<abbr title="Symfony\Component\HttpFoundation\Request">Request</abbr>))

    <br />
    in /private/var/www/loogares/web/app_dev.php at line 25&nbsp;
    <a href="#" onclick="toggle('trace_0_7'); switchIcons('icon_0_7_open', 'icon_0_7_close'); return false;"><img class="toggle" id="icon_0_7_close" alt="-" src="/loogares/web/bundles/framework/images/blue_picto_less.gif" style="visibility: hidden" /><img class="toggle" id="icon_0_7_open" alt="+" src="/loogares/web/bundles/framework/images/blue_picto_more.gif" style="visibility: display; margin-left: -18px" /></a>    <div id="trace_0_7" style="display: none" class="trace">
        <ol start="22"><li><code></code></li>
<li><code><span style="color: #0000BB">$kernel&nbsp;</span><span style="color: #007700">=&nbsp;new&nbsp;</span><span style="color: #0000BB">AppKernel</span><span style="color: #007700">(</span><span style="color: #DD0000">'dev'</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">true</span><span style="color: #007700">);</span></code></li>
<li><code><span style="color: #0000BB">$kernel</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">loadClassCache</span><span style="color: #007700">();</span></code></li>
<li class="selected"><code><span style="color: #0000BB">$kernel</span><span style="color: #007700">-&gt;</span><span style="color: #0000BB">handle</span><span style="color: #007700">(</span><span style="color: #0000BB">Request</span><span style="color: #007700">::</span><span style="color: #0000BB">createFromGlobals</span><span style="color: #007700">())-&gt;</span><span style="color: #0000BB">send</span><span style="color: #007700">();</span></code></li>
<li><code>
</code></li></ol>
    </div>
            </li>
            </ol>
</div>
    
            <div class="block">
            <div class="logs clear_fix">
                <h2>
                    Logs&nbsp;
                    <a href="#" onclick="toggle('logs'); switchIcons('icon_logs_open', 'icon_logs_close'); return false;"><img class="toggle" id="icon_logs_open" alt="+" src="/loogares/web/bundles/framework/images/blue_picto_more.gif" style="visibility: hidden" /><img class="toggle" id="icon_logs_close" alt="-" src="/loogares/web/bundles/framework/images/blue_picto_less.gif" style="visibility: visible; margin-left: -18px" /></a></h2>
                                    <div class="error_count">
                        <span>
                            1 error
                        </span>
                    </div>
                
            </div>

            <div id="logs">
                <ol class="traces logs">
            <li>
            Notified event &quot;kernel.request&quot; to listener &quot;Symfony\Component\HttpKernel\EventListener\ProfilerListener::onKernelRequest&quot;.
        </li>
            <li>
            Notified event &quot;kernel.request&quot; to listener &quot;Symfony\Bundle\FrameworkBundle\EventListener\RouterListener::onEarlyKernelRequest&quot;.
        </li>
            <li>
            Notified event &quot;kernel.request&quot; to listener &quot;Symfony\Bundle\FrameworkBundle\EventListener\SessionListener::onKernelRequest&quot;.
        </li>
            <li>
            Notified event &quot;kernel.request&quot; to listener &quot;Symfony\Component\Security\Http\Firewall::onKernelRequest&quot;.
        </li>
            <li>
            Populated SecurityContext with an anonymous Token
        </li>
            <li>
            Notified event &quot;kernel.request&quot; to listener &quot;Symfony\Bundle\FrameworkBundle\EventListener\RouterListener::onKernelRequest&quot;.
        </li>
            <li>
            Matched route &quot;_utilMail&quot; (parameters: &quot;_controller&quot;: &quot;Loogares\LugarBundle\Controller\AjaxController::utilMailAction&quot;, &quot;_route&quot;: &quot;_utilMail&quot;)
        </li>
            <li>
            Notified event &quot;kernel.request&quot; to listener &quot;Symfony\Bundle\AsseticBundle\EventListener\RequestListener::onKernelRequest&quot;.
        </li>
            <li>
            Notified event &quot;kernel.request&quot; to listener &quot;Stof\DoctrineExtensionsBundle\Listener\TranslationListener::onKernelRequest&quot;.
        </li>
            <li>
            Notified event &quot;kernel.controller&quot; to listener &quot;Loogares\ExtraBundle\EventListener\BeforeControllerListener::onKernelController&quot;.
        </li>
            <li>
            SET NAMES UTF8 ([])
        </li>
            <li>
            SELECT t0.id AS id1, t0.nombre AS nombre2, t0.slug AS slug3, t0.mapa AS mapa4, t0.mostrar_lugar AS mostrar_lugar5, t0.pais_id AS pais_id6 FROM ciudad t0 WHERE t0.slug = ? ([&quot;santiago-de-chile&quot;])
        </li>
            <li>
            SELECT t0.id AS id1, t0.nombre AS nombre2, t0.slug AS slug3, t0.mapa AS mapa4, t0.codigo_area AS codigo_area5, t0.locale AS locale6, t0.mostrar_lugar AS mostrar_lugar7 FROM pais t0 WHERE t0.id = ? ([&quot;1&quot;])
        </li>
            <li>
            Notified event &quot;kernel.controller&quot; to listener &quot;Symfony\Bundle\FrameworkBundle\DataCollector\RequestDataCollector::onKernelController&quot;.
        </li>
            <li>
            Notified event &quot;kernel.controller&quot; to listener &quot;Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener::onKernelController&quot;.
        </li>
            <li>
            Notified event &quot;kernel.controller&quot; to listener &quot;Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener::onKernelController&quot;.
        </li>
            <li>
            Notified event &quot;kernel.controller&quot; to listener &quot;Sensio\Bundle\FrameworkExtraBundle\EventListener\TemplateListener::onKernelController&quot;.
        </li>
            <li>
            Notified event &quot;kernel.controller&quot; to listener &quot;JMS\SecurityExtraBundle\Controller\ControllerListener::onCoreController&quot;.
        </li>
            <li>
            Notified event &quot;kernel.exception&quot; to listener &quot;Symfony\Component\Security\Http\Firewall\ExceptionListener::onKernelException&quot;.
        </li>
            <li>
            Notified event &quot;kernel.exception&quot; to listener &quot;Symfony\Component\HttpKernel\EventListener\ProfilerListener::onKernelException&quot;.
        </li>
            <li>
            Notified event &quot;kernel.exception&quot; to listener &quot;Symfony\Component\HttpKernel\EventListener\ExceptionListener::onKernelException&quot;.
        </li>
            <li class="error">
            ErrorException: Notice: Undefined index: recomendacion in /private/var/www/loogares/src/Loogares/LugarBundle/Controller/AjaxController.php line 445 (uncaught exception) at /private/var/www/loogares/vendor/symfony/src/Symfony/Component/HttpKernel/Debug/ErrorHandler.php line 65
        </li>
            <li>
            Notified event &quot;kernel.request&quot; to listener &quot;Symfony\Component\HttpKernel\EventListener\ProfilerListener::onKernelRequest&quot;.
        </li>
            <li>
            Notified event &quot;kernel.request&quot; to listener &quot;Symfony\Bundle\FrameworkBundle\EventListener\RouterListener::onEarlyKernelRequest&quot;.
        </li>
            <li>
            Notified event &quot;kernel.request&quot; to listener &quot;Symfony\Bundle\FrameworkBundle\EventListener\SessionListener::onKernelRequest&quot;.
        </li>
            <li>
            Notified event &quot;kernel.request&quot; to listener &quot;Symfony\Component\Security\Http\Firewall::onKernelRequest&quot;.
        </li>
            <li>
            Notified event &quot;kernel.request&quot; to listener &quot;Symfony\Bundle\FrameworkBundle\EventListener\RouterListener::onKernelRequest&quot;.
        </li>
            <li>
            Notified event &quot;kernel.request&quot; to listener &quot;Symfony\Bundle\AsseticBundle\EventListener\RequestListener::onKernelRequest&quot;.
        </li>
            <li>
            Notified event &quot;kernel.request&quot; to listener &quot;Stof\DoctrineExtensionsBundle\Listener\TranslationListener::onKernelRequest&quot;.
        </li>
            <li>
            Notified event &quot;kernel.controller&quot; to listener &quot;Loogares\ExtraBundle\EventListener\BeforeControllerListener::onKernelController&quot;.
        </li>
            <li>
            Notified event &quot;kernel.controller&quot; to listener &quot;Symfony\Bundle\FrameworkBundle\DataCollector\RequestDataCollector::onKernelController&quot;.
        </li>
            <li>
            Notified event &quot;kernel.controller&quot; to listener &quot;Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener::onKernelController&quot;.
        </li>
            <li>
            Notified event &quot;kernel.controller&quot; to listener &quot;Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener::onKernelController&quot;.
        </li>
            <li>
            Notified event &quot;kernel.controller&quot; to listener &quot;Sensio\Bundle\FrameworkExtraBundle\EventListener\TemplateListener::onKernelController&quot;.
        </li>
            <li>
            Notified event &quot;kernel.controller&quot; to listener &quot;JMS\SecurityExtraBundle\Controller\ControllerListener::onCoreController&quot;.
        </li>
    </ol>
            </div>

        </div>
    
    
</div>

<script type="text/javascript">//<![CDATA[
    function toggle(id, clazz) {
        var el = document.getElementById(id),
            current = el.style.display,
            i;

        if (clazz) {
            var tags = document.getElementsByTagName('*');
            for (i = tags.length - 1; i >= 0 ; i--) {
                if (tags[i].className === clazz) {
                    tags[i].style.display = 'none';
                }
            }
        }

        el.style.display = current === 'none' ? 'block' : 'none';
    }

    function switchIcons(id1, id2) {
        var icon1, icon2, visibility1, visibility2;

        icon1 = document.getElementById(id1);
        icon2 = document.getElementById(id2);

        visibility1 = icon1.style.visibility;
        visibility2 = icon2.style.visibility;

        icon1.style.visibility = visibility2;
        icon2.style.visibility = visibility1;
    }
//]]></script>
        </div>
    
<div id="sfwdt501036e523e9a" style="display: none"></div><script type="text/javascript">/*<![CDATA[*/    (function () {        var wdt, xhr;        wdt = document.getElementById('sfwdt501036e523e9a');        if (window.XMLHttpRequest) {            xhr = new XMLHttpRequest();        } else {            xhr = new ActiveXObject('Microsoft.XMLHTTP');        }        xhr.open('GET', '/loogares/web/app_dev.php/_wdt/501036e523e9a', true);        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');        xhr.onreadystatechange = function(state) {            if (4 === xhr.readyState && 200 === xhr.status && -1 !== xhr.responseText.indexOf('sf-toolbarreset')) {                wdt.innerHTML = xhr.responseText;                wdt.style.display = 'block';            }        };        xhr.send('');    })();/*]]>*/</script>
</body>
</html>
