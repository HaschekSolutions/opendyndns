<?php if((is_array($hostdata['advanceddns']) && count($hostdata['advanceddns']))||$hostdata['ipv4'] || $hostdata['ipv6']) : ?>
<table role="grid">
    <thead>
        <tr>
            <th scope="col">Hostname</th>
            <th scope="col">Type</th>
            <th scope="col">Value</th>
            <th scope="col">Delete</th>
        </tr>
    </thead>
    <tbody>
    <?php if($hostdata['ipv4']) : ?>
            <tr>
                <td><?=$fulldomain?></td>
                <td>A</td>
                <td><?= $hostdata['ipv4'] ?></td>
                <td>set via API</td>
            </tr>
        <?php endif; ?>
        <?php if($hostdata['ipv6']) : ?>
            <tr>
                <td><?=$fulldomain?></td>
                <td>AAAA</td>
                <td><?= $hostdata['ipv6'] ?></td>
                <td>set via API</td>
            </tr>
        <?php endif; ?>
        <?php foreach ($hostdata['advanceddns'] as $key => $entry) : ?>
            <tr>
                <td><?php if($entry['hostname']!='@'): ?><?= $entry['hostname'] ?>.<?php endif; ?><?=$fulldomain?></td>
                <td><?= $entry['type'] ?></td>
                <td><input type="text" value="<?= escape($entry['value']) ?>" disabled></td>
                <td><button hx-get="/htmx/deletedns/<?= $fulldomain ?>/<?= $key ?>" hx-confirm="Do you really want to delete this entry?" hx-target="#advanced"><i class="fas fa-trash"></i></button></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>