<?php if(is_array($hostdata['advanceddns']) && count($hostdata['advanceddns'])) : ?>
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
        <?php foreach ($hostdata['advanceddns'] as $key => $entry) : ?>
            <tr>
                <td><?= $entry['hostname'] ?></td>
                <td><?= $entry['type'] ?></td>
                <td><?= $entry['value'] ?></td>
                <td><button hx-get="/api/deletedns/<?= $fulldomain ?>?hostname=<?= $entry['hostname'] ?>&type=<?= $entry['type'] ?>" hx-target="#advanced"><i class="fas fa-trash"></i></button></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>