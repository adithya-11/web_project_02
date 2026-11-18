<fieldset class="pattern-input">
    <legend class="field-label"><?= e($gridLegend) ?></legend>
    <div class="grid grid--input">
        <?php for ($cell = 0; $cell < 9; $cell++): ?>
            <label class="toggle" style="--c:<?= e($cell) ?>">
                <input type="checkbox" name="cells[]" value="<?= e($cell) ?>"
                    <?= in_array($cell, $checkedCells, true) ? 'checked' : '' ?>>
                <span class="sr-only">Row <?= e(intdiv($cell, 3) + 1) ?>, column <?= e(($cell % 3) + 1) ?></span>
            </label>
        <?php endfor; ?>
    </div>
</fieldset>
