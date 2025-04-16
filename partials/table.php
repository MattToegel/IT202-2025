<?php
/**
 * Renders a dynamic, Bootstrap-compatible HTML table using the provided `$data` configuration array.
 *
 * Supports:
 * - Dynamic headers from data or manually overridden
 * - Optional action columns: View, Edit, Delete, and POST-backed custom form
 * - HTML-safe rendering with optional raw HTML column output
 * - Ignored columns for hiding sensitive or unnecessary fields
 * - Configurable classes for the table and action buttons
 * - Graceful empty-state handling
 *
 * `$data` structure:
 * - title: string (optional) - table title (renders as `<h3>`)
 * - table_class: string (optional) - main table class (default: "table")
 * - extra_classes: string (optional) - additional classes for the table element
 * - data: array - an array of associative arrays (records/rows)
 * - header_override: array|string (optional) - array or CSV string of column headers to use instead of keys from the first row
 * - ignored_columns: array|string (optional) - columns to omit from rendering (array or CSV string)
 * - html_columns: array (optional) - keys for columns that contain trusted HTML and should not be escaped
 * - view_url: string (optional) - base URL for "View" action
 * - view_label: string (default: "View") - label for "View" button
 * - view_classes: string (default: "btn btn-primary") - class for "View" button
 * - edit_url: string (optional) - base URL for "Edit" action
 * - edit_label: string (default: "Edit") - label for "Edit" button
 * - edit_classes: string (default: "btn btn-secondary") - class for "Edit" button
 * - delete_url: string (optional) - base URL for "Delete" action
 * - delete_label: string (default: "Delete") - label for "Delete" button
 * - delete_classes: string (default: "btn btn-danger") - class for "Delete" button
 * - primary_key: string (default: "id") - column name to use for identifying the record in action URLs
 * - post_self_form: array (optional) - associative array for a POST button instead of an action URL. Accepts:
 *     - name: string - name of the hidden input
 *     - label: string - value of the submit button
 *     - classes: string - class for the submit button
 * - empty_message: string (default: "No records to show") - shown when `data` is empty
 *
 * Example usage:
 * ```
 * include "table.php";
 * $data = [
 *   "title" => "Users",
 *   "data" => [
 *     ["id" => 1, "name" => "Alice", "email" => "alice@example.com"],
 *     ["id" => 2, "name" => "Bob", "email" => "bob@example.com"]
 *   ],
 *   "view_url" => "/user/view",
 *   "edit_url" => "/user/edit",
 *   "delete_url" => "/user/delete",
 *   "ignored_columns" => "email",
 *   "html_columns" => ["name"]
 * ];
 * ```
 */
?>

<?php if (isset($data)) : ?>
    <?php
    //setup some variables for readability
    $_extra_classes = se($data, "extra_classes", "", false);
    $_table_class = se($data, "table_class", "table", false);
    $_title = se($data, "title", "", false);
    $_data = isset($data["data"]) ? $data["data"] : [];
    if (!$_data) {
        $_data = [];
    }
    $_html_columns = isset($data["html_columns"]) ? (array)$data["html_columns"] : [];
    $_view_url = se($data, "view_url", "", false);
    $_view_label = se($data, "view_label", "View", false);
    $_view_classes = se($data, "view_classes", "btn btn-primary", false);
    $_edit_url = se($data, "edit_url", "", false);
    $_edit_label = se($data, "edit_label", "Edit", false);
    $_edit_classes = se($data, "edit_classes", "btn btn-secondary", false);
    $_delete_url = se($data, "delete_url", "", false);
    $_delete_label = se($data, "delete_label", "Delete", false);
    $_delete_classes = se($data, "delete_classes", "btn btn-danger", false);
    $_primary_key_column = se($data, "primary_key", "id", false); // used for the url generation
    //TODO persist query params (future lesson)
    //
    // edge case that should consider a redesign
    $_post_self_form = isset($data["post_self_form"]) ? $data["post_self_form"] : [];
    // end edge case
    $_has_atleast_one_url = $_view_url || $_edit_url || $_delete_url || $_post_self_form;
    $_empty_message = se($data, "empty_message", "No records to show", false);
    $_header_override = isset($data["header_override"]) ? $data["header_override"] : []; // note: this is as csv string or an array
    // assumes csv list; explodes to array
    if (is_string($_header_override)) {
        $_header_override = explode(",", $_header_override);
    }
    $_ignored_columns = isset($data["ignored_columns"]) ? $data["ignored_columns"] : []; // note: this is as csv string or an array
    // assumes csv list; explodes to array
    if (is_string($_ignored_columns)) {
        $_ignored_columns = explode(",", $_ignored_columns);
    }
    // attempt to get headers from $_data if no override
    if (!$_header_override && count($_data) > 0) {
        $_header_override = array_filter(array_keys($_data[0]), function ($v) use ($_ignored_columns) {
            return !in_array($v, $_ignored_columns);
        });
    }

    ?>
    <?php if ($_title) : ?>
        <h3><?php se($_title); ?></h3>
    <?php endif; ?>
    <table class="<?php se($_table_class); ?> <?php se($_extra_classes); ?>">
        <?php if ($_header_override) : ?>
            <thead>
                <tr>
                    <?php foreach ($_header_override as $h) : ?>
                        <th><?php se($h); ?></th>
                    <?php endforeach; ?>
                    <?php if ($_has_atleast_one_url) : ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
        <?php endif; ?>
        <tbody>
            <?php if (is_array($_data) && count($_data) > 0) : ?>
                <?php foreach ($_data as $row) : ?>
                    <tr>
                        <?php foreach ($row as $k => $v) : ?>
                            <?php if (!in_array($k, $_ignored_columns)) : ?>
                                <td><?php
                                    if (in_array($k, $_html_columns)) {
                                        echo $v; // assume trusted HTML
                                    } else {
                                        se($v); // assume untrusted data
                                    }
                                    ?>
                                </td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if ($_has_atleast_one_url) : ?>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <?php if ($_view_url) : ?>
                                        <a href="<?php se($_view_url); ?>?<?php se($_primary_key_column); ?>=<?php se($row, $_primary_key_column); ?>" class="<?php se($_view_classes); ?>"><?php se($_view_label); ?></a>
                                    <?php endif; ?>
                                    <?php if ($_edit_url) : ?>
                                        <a href="<?php se($_edit_url); ?>?<?php se($_primary_key_column); ?>=<?php se($row, $_primary_key_column); ?>" class="<?php se($_edit_classes); ?>"><?php se($_edit_label); ?></a>
                                    <?php endif; ?>
                                    <?php if ($_delete_url) : ?>
                                        <a href="<?php se($_delete_url); ?>?<?php se($_primary_key_column); ?>=<?php se($row, $_primary_key_column); ?>" class="<?php se($_delete_classes); ?>"><?php se($_delete_label); ?></a>
                                    <?php endif; ?>
                                    <?php if ($_post_self_form) : ?>
                                        <!-- TODO refactor -->
                                        <form method="POST">
                                            <input type="hidden" name="<?php se($_post_self_form, "name", $_primary_key_column); ?>" value="<?php se($row, $_primary_key_column); ?>" />
                                            <input type="submit" class="<?php se($_post_self_form, "classes"); ?>" value="<?php se($_post_self_form, "label", "Submit"); ?>" />
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="100%"><?php se($_empty_message); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php
unset(
    $_table_class,
    $_extra_classes,
    $_title,
    $_data,
    $_view_url,
    $_view_label,
    $_view_classes,
    $_edit_url,
    $_edit_label,
    $_edit_classes,
    $_delete_url,
    $_delete_label,
    $_delete_classes,
    $_primary_key_column,
    $_post_self_form,
    $_has_atleast_one_url,
    $_empty_message,
    $_header_override,
    $_ignored_columns
);
?>