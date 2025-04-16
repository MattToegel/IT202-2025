<?php

/**
 * Renders a dynamic Bootstrap 5 form field (input, textarea, or select) using `$data` array configuration.
 *
 * Supports:
 * - Standard inputs (`text`, `email`, `number`, etc.)
 * - Textarea
 * - Select dropdowns with options and placeholder
 * - Optional label
 * - Optional margin wrapping (`include_margin`)
 * - Custom HTML attributes via `rules`
 * - Automatic escaping and sanitization via `se()`
 *
 * `$data` structure (associative array):
 * - type: string - field type ("text", "select", "textarea", etc.)
 * - name: string - input name
 * - id: string (optional) - field ID (auto-generated if omitted)
 * - label: string (optional) - label text
 * - value: string (optional) - selected or input value
 * - placeholder: string (optional) - placeholder text
 * - class: string (optional) - override default Bootstrap class
 * - include_margin: bool|string (optional) - wrap with margin div (true by default)
 * - rules: array (optional) - key-value pairs of HTML attributes (e.g., ["required" => true, "maxlength" => 30])
 * - options: array (for select only) - key-value pairs for select options
 *
 * Example usage:
 * ```
 * $data = [
 *   "type" => "select",
 *   "name" => "country",
 *   "label" => "Select Country",
 *   "value" => "US",
 *   "placeholder" => "Choose...",
 *   "options" => ["US" => "United States", "CA" => "Canada"],
 *   "rules" => ["required" => true]
 * ];
 * include "form_field.php";
 * ```
 */
?>
<?php if (isset($data)) : ?>
    <?php

    //setup some variables for readability
    $_include_margin = filter_var(se($data, "include_margin", true, false), FILTER_VALIDATE_BOOLEAN);
    $_label = se($data, "label", "", false);
    $_id = se($data, "id", uniqid(), false);
    $_type = se($data, "type", "text", false);
    $_placeholder = se($data, "placeholder", "", false);
    $_value = se($data, "value", "", false);
    $_name = se($data, "name", "", false);
    $_class = se($data, "class", $_type === "select" ? "form-select" : "form-control", false);
    $_non_standard_types = ["select", "radio", "checkbox", "toggle", "switch", "range", "textarea"]; //add more as necessary
    $_rules = isset($data["rules"]) ? $data["rules"] : []; // Can't use se() here since se() doesn't support returning complex data types (i.e., arrays);
    //map rules to key="value"
    $_rules = array_map(function ($key, $value) {
        if ($value === true) {
            return htmlspecialchars($key, ENT_QUOTES);
        }
        return htmlspecialchars($key, ENT_QUOTES) . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
    }, array_keys($_rules), $_rules);
    //convert array to a space separate string
    $_rules = implode(" ", $_rules);
    $_options = isset($data["options"]) && is_array($data["options"]) ? $data["options"] : [];

    ?>
    <?php /* Include margin open tag */ ?>
    <?php if ($_include_margin) : ?>
        <div class="mb-3">
        <?php endif; ?>
        <?php if ($_label) : ?>
            <?php /* label field */ ?>
            <label class="form-label" for="<?php se($_id); ?>"><?php se($_label); ?></label>
        <?php endif; ?>

        <?php if (!in_array($_type, $_non_standard_types)) : ?>
            <?php /* input field */ ?>
            <input type="<?php se($_type); ?>" name="<?php se($_name); ?>" class="<?php se($_class); ?>" id="<?php se($_id); ?>" value="<?php se($_value); ?>" placeholder="<?php se($_placeholder); ?>"
                <?php echo $_rules; ?> />
        <?php elseif ($_type === "textarea"): ?>
            <textarea class="<?php se($_class); ?>" name="<?php se($_name); ?>" id="<?php se($_id); ?>" placeholder="<?php se($_placeholder); ?>" <?php echo $_rules; ?>><?php se($_value); ?></textarea>
        <?php elseif ($_type == "select") : ?>
            <select class="<?php se($_class); ?>" name="<?php se($_name); ?>" id="<?php se($_id); ?>">
                <?php if ($_placeholder): ?>
                    <option disabled <?php echo empty($_value) ? 'selected' : ''; ?> value=""><?php se($_placeholder); ?></option>
                <?php endif; ?>
                <?php foreach ($_options as $opt) : ?>
                    <?php foreach($opt as $k => $v) : ?>
                        <option <?php echo (isset($_value) && $_value == $k ? "selected" : ""); ?> value="<?php se($k); ?>"><?php se($v); ?></option>
                   <?php endforeach; ?>
                <?php endforeach; ?>
            </select>
        <?php elseif ($_type === "checkbox") : ?>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="<?php se($_name); ?>" id="<?php se($_id); ?>"
                    value="1"
                    <?php echo $_rules; ?>
                    <?php echo ($_value ? 'checked' : ''); ?>>
                <?php if ($_label): ?>
                    <label class="form-check-label" for="<?php se($_id); ?>"><?php se($_label); ?></label>
                <?php endif; ?>
            </div>
        <?php elseif ($_type === "TBD type") : ?>
            <?php /* TODO other non-form-control elements */ ?>
        <?php endif; ?>
        <?php /* Include margin close tag */ ?>
        <?php if ($_include_margin) : ?>
        </div>
    <?php endif; ?>
    <?php
    //cleanup just in case this is used directly instead of via render_button()
    // if it's used from the function, the variables will be out of scope when the function is done so there'd be no need to unset them
    unset($_include_margin);
    unset($_label);
    unset($_id);
    unset($_type);
    unset($_placeholder);
    unset($_value);
    unset($_name);
    unset($_non_standard_types);
    unset($_rules);
    unset($_options);
    unset($_class);

    ?>
<?php endif; ?>