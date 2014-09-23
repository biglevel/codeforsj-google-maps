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
        <legend>Map Shapes</legend>
        <label for="delimiter">
        <span id="download_files">Zip Code Shapes</span>
        <a href="/admin/download?map_id=<?php echo $this->map_id; ?>">Download</a>
        </label>
    </fieldset>
    <?php endif; ?>
    <fieldset>
        <legend>Actions</legend>
        <button type="submit" name="submit">Save</button>
    </fieldset>
</form>