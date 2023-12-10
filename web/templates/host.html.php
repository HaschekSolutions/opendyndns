<?php if($hostdata['password'] && $_REQUEST['password'] != $hostdata['password'] && $_SESSION[$fulldomain] !==true): ?>
    
    <form hx-post="/htmx/host" hx-target="#main">
        <h3>This host has a password configured. Please enter it</h3>
        <input type="hidden" name="hostname" value="<?= $hostname ?>">
        <input type="hidden" name="domain" value="<?= $domain ?>">
        <input type="password" name="password" placeholder="Password">
        <input type="submit" value="Login">
    </form>
<?php return;
    elseif(($hostdata['password'] && $_REQUEST['password'] == $hostdata['password']) || !$hostdata['password']):
        $_SESSION[$fulldomain] = true;
    elseif($hostdata['password'] && $_REQUEST['password'] !='' && $_REQUEST['password'] != $hostdata['password']):
        echo error('Invalid password');
        return;
    endif; ?>


<article>
    <header><?= $fulldomain ?></header>
    <form hx-post="/htmx/host" hx-target="#main">
        <input type="hidden" name="hostname" value="<?= $hostname ?>">
        <input type="hidden" name="domain" value="<?= $domain ?>">

        <label>(optional) Password to protect this host: <input name="password" value="<?= $hostdata['password'] ?>"></label>
        <label>Note: <input type="text" name="note" value="<?= escape($hostdata['note']) ?>"></label>
        <input type="submit" name="savedata" value="Save">
        <div id="ips">
            <label>IPv4: <?= escape($hostdata['ipv4'])?:'Not set' ?></label>
            <label>IPv6: <?= escape($hostdata['ipv6'])?:'Not set' ?></label>
        </div>
        <button hx-get="/htmx/updateip/<?=$fulldomain?>" hx-target="#ips">Set to current IP (<?= getUserIP()?>)</button>
        <label>Last updated: <?= escape($hostdata['lastupdated']?:'Never') ?></label>
        <details>
            <summary>Show secret</summary>
            <div id="the-secret"><?=$hostdata['secret'];?></div>
            <button hx-get="/api/renewsecret/<?=$fulldomain;?>?secret=<?=$hostdata['secret']?>" hx-target="#the-secret"><i class="fas fa-sync-alt"></i> Renew secret</button>
        </details>
        
    </form>

    <h3>How to use</h3>

    <h6>Auto detect your IP</h6>
    <pre><code class="language-curl">curl <?= $url ?>/api/setip/<?= $fulldomain?> \
-H "secret:<?= $hostdata['secret']?>"</code></pre>

    <h6>Or tell the API which IPs to use</h6>
    <pre><code class="language-curl">curl <?= $url ?>/api/setip/<?= $fulldomain?> \
-H "secret:<?= $hostdata['secret']?>" \
--data "ipv4=1.1.1.1" \
--data "ipv6=2001:4860:4860::8888"</code></pre>

    <h6>Automate the IP update</h6>
    To automate the process you need to run the curl command every 5 minutes or so. You can use a cronjob for that.
    <pre><code class="language-bash">crontab -e</code></pre>
    Add this line to the crontab:
    <pre><code class="language-bash">*/5 * * * * curl <?= $url ?>/api/setip/<?= $fulldomain?> -H "secret:<?= $hostdata['secret']?>"</code></pre>
    
    <h6>Clear IP addresses</h6>
    <pre><code class="language-curl">curl <?= $url ?>/api/clearips/<?= $fulldomain?> \
-H "secret:<?= $hostdata['secret']?>"</code></pre>
</article>

<script>hljs.highlightAll();</script>