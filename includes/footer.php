<?php
/**
 * Pie común: cierra el layout e incluye los scripts.
 * Variable opcional antes de incluir:
 *   $js_extra (array) - rutas adicionales de JS específicas de la página.
 */
$js_extra = $js_extra ?? [];
?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
<script src="assets/js/app.js"></script>
<script src="assets/js/autoguardado.js"></script>
<?php foreach ($js_extra as $ruta): ?>
    <script src="<?= escapar_texto($ruta) ?>"></script>
<?php endforeach; ?>
</body>
</html>
