<p>
    <a href="/admin/map">Add</a>
</p>
<table>
    <caption>Maps</caption>
    <thead>
        <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Center Latitude</th>
            <th>Center Longitude</th>
            <th>Center Zoom</th>
            <th>View</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($this->maps as $row): ?>
        <tr>
            <td><a href="/admin/map?map_id=<?php echo $row->map_id; ?>"><?php echo $row->name; ?></a></td>
            <td><?php echo $row->type; ?></td>
            <td><?php echo $row->center_latitude; ?></td>
            <td><?php echo $row->center_longitude; ?></td>
            <td><?php echo $row->center_zoom; ?></td>
            <td><a href="/?map_id=<?php echo $row->map_id; ?>" target="_blank">View Map</a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
