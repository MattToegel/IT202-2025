<?php if (isset($data)) : ?>
    <?php
    //Setup variables for readability and fallback values
    $_btn_type = se($data, "type", "button", false);
    $_btn_text = se($data, "text", "Button", false);
    $_btn_color = se($data, "color", "primary", false);
    // onclick support
    $_btn_onclick = se($data, "onClick", se($data, "onclick", "", false), false);
    $_onclick_attr = $_btn_onclick ? ' onclick="' . htmlspecialchars($_btn_onclick, ENT_QUOTES) . '"' : '';

    ?>
    <?php if ($_btn_type === "button") : ?>
        <button type="button" class="btn btn-<?php se($_btn_color); ?>"><?php se($_btn_text); ?></button>
    <?php elseif ($_btn_type === "submit") : ?>
        <input type="submit" class="btn btn-<?php se($_btn_color); ?>" value="<?php se($_btn_text); ?>" />
    <?php endif; ?>

    <?php
    //cleanup just in case this is used directly instead of via render_button()
    // if it's used from the function, the variables will be out of scope when the function is done so there'd be no need to unset them
    unset($_btn_type);
    unset($_btn_text);
    unset($_btn_color);
    ?>
<?php endif; ?>