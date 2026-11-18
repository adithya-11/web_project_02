<fieldset>
    <legend><?= e($gridLegend) ?></legend>
    <div class="grid">
        <?php for ($cell = 0; $cell < 9; $cell++): ?>
            <label>
                <input type="checkbox" name="cells[]" value="<?= e($cell) ?>"
                    aria-label="Row <?= e(intdiv($cell, 3) + 1) ?>, column <?= e(($cell % 3) + 1) ?>"
                    <?= in_array($cell, $checkedCells, true) ? 'checked' : '' ?>>
            </label>
        <?php endfor; ?>
    </div>
</fieldset>
