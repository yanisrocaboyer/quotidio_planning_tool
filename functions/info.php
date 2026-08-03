<?php
function info($text) {
    ?>
    <p class="info-text ml-1"><span class="material-symbols-outlined">info</span></p>
    <style>
    .info-text {
        position: relative;
        cursor: pointer;
    }
    .info-text:hover::after {
        content: "<?php echo addslashes($text); ?>";
        position: absolute;
        left: 120%;
        transform: translateY(-50%);
        background: #222;
        color: #fff;
        padding: 5px 10px;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        white-space: pre-line;
        z-index: 100;
        min-width: max-content;
    }
    </style>
    <?php
}
?>