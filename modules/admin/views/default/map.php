<p>
    <a href="/admin">Maps</a> | <a href="/admin/map">Add Map</a> | <a href="/admin/candidates">Generate Candidate JSON</a>
</p>
<form action="/admin/map<?php echo (is_numeric($this->map_id)) ? '?map_id=' . $this->map_id : ''; ?>" method="post">
    <input type="hidden" name="" value="" />
    <?php if ($this->form->errors > 0): ?>
    <div>
        <?php echo $this->form->showErrors(); ?>
    </div>
    <?php endif; ?>
    <fieldset>
        <legend>Map Information</legend>
        
        <?php foreach(array('name','type','center_latitude','center_longitude','center_zoom','colors') as $key): ?>
        <label for="<?php echo $key; ?>">
            <?php echo $this->form->$key->label(); ?>
            <?php echo $this->form->$key->field(); ?>
        </label>
        <?php endforeach; ?>
    </fieldset>
    <?php if(is_numeric($this->map_id)): ?>
    <fieldset>
        <legend>Map Data</legend>
        <p>
            Columns:
        </p>
        <ol>
            <li>Type: primary, runoff, pac</li>
            <li>Candidate Name</li>
            <li>Zip</li>
            <li>Amount</li>
        </ol>
        <p>Example of data</p>
        <pre>primary,Nguyen,10012,250.00
primary,Licarrdo,19711,1000.00
primary,Oliverio,11414,1434.60
primary,Cortese,94086,1350.00
primary,Herrera,20007,1000.00
runoff,Licarrdo,19711,1000.00
runoff,Cortese,94086,1350.00
pac,Licarrdo,19711,1000.00
pac,Cortese,94086,1350.00
        </pre>
        <label for="delimiter">
            <?php echo $this->form->delimiter->label(); ?>
            <?php echo $this->form->delimiter->field(); ?>
        </label>
        <label for="contributions">
            <?php echo $this->form->contributions->label(); ?>
            <?php echo $this->form->contributions->field(); ?>
        </label>
    </fieldset>
    <fieldset>
        <legend>Map v2.0</legend>
        <label for="download">Zip Code Shapes</label>
        <a href="/admin/download?map_id=<?php echo $this->map_id; ?>">Download</a>
        <label for="contributions">Contributions JSON</label>
        <textarea style="width: 640px; height: 320px;"><?php echo $this->data; ?></textarea>
        </label>
    </fieldset>
    <?php endif; ?>
    <fieldset>
        <legend>Actions</legend>
        <button type="submit" name="submit">Save</button>
    </fieldset>
</form>