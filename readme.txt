=== Gutenberg Utility Classes ===
Contributors:      maximilianhuhle
Tags:              gutenberg, block editor, responsive, utility classes, css
Requires at least: 6.3
Tested up to:      6.7
Requires PHP:      8.0
Stable tag:        1.0.0
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Responsive CSS utility classes for the Gutenberg Block Editor.

== Description ==

Gutenberg Utility Classes adds a set of responsive helper classes to the
WordPress Block Editor (Gutenberg) and the frontend. Use them directly in
the "Additional CSS class(es)" field of any block to control visibility,
layout stacking, column widths, flex order, spacing, and text alignment –
all per breakpoint.

Breakpoints follow WordPress/Gutenberg core conventions:

  Mobile:   max-width: 599 px
  Tablet:   min-width: 600 px and max-width: 781 px
  Desktop:  min-width: 782 px

= Class Reference =

--- 1. VISIBILITY ---

Hide on a specific device:

  Class               Effect
  ------------------- --------------------------------------
  hide-on-mobile      display: none on Mobile
  hide-on-tablet      display: none on Tablet
  hide-on-desktop     display: none on Desktop

Show only on one device (hidden on all other breakpoints):

  Class               Visible on
  ------------------- --------------------------------------
  show-on-mobile      Mobile only
  show-on-tablet      Tablet only
  show-on-desktop     Desktop only

--- 2. STACKING ---

Targets: .wp-block-columns, .wp-block-group.is-layout-flex,
         .wp-block-group.is-layout-row

Stack on a single breakpoint:

  Class               flex-direction: column on
  ------------------- --------------------------------------
  stack-on-mobile     Mobile only
  stack-on-tablet     Tablet only
  stack-on-desktop    Desktop only

Stack from a breakpoint upward:

  Class               Stacked on
  ------------------- --------------------------------------
  stack-from-mobile   Mobile + Tablet + Desktop (always)
  stack-from-tablet   Tablet + Desktop
  stack-from-desktop  Desktop only (same as stack-on-desktop)

Note: stack-on-desktop and stack-from-desktop are identical and both
included for semantic clarity.

--- 3. WIDTH UTILITIES ---

Schema: .width-{value}-{breakpoint}

Values: 10, 20, 25, 30, 33, 40, 50, 60, 66, 70, 75, 80, 90, 100, auto
Breakpoints: mobile, tablet, desktop

Examples:

  Class                 Effect
  --------------------- -----------------------------------------
  width-50-mobile       50 % wide on Mobile
  width-33-tablet       33 % wide on Tablet
  width-100-desktop     100 % wide on Desktop
  width-auto-mobile     width: auto; flex-basis: auto on Mobile

--- 4. ORDER (Flex Order) ---

Schema: .order-{value}-{breakpoint}

  Class                 order value   Breakpoint
  --------------------- ------------- ----------
  order-first-mobile    -1            Mobile
  order-1-mobile         1            Mobile
  order-2-mobile         2            Mobile
  order-3-mobile         3            Mobile
  order-4-mobile         4            Mobile
  order-5-mobile         5            Mobile
  order-last-mobile     99            Mobile
  (same pattern for -tablet and -desktop)

--- 5. SPACING ---

  Class               Effect
  ------------------- -------------------------------------------------
  no-gap-mobile       gap: 0 on .wp-block-columns, Mobile
  no-gap-tablet       gap: 0 on .wp-block-columns, Tablet
  no-gap-desktop      gap: 0 on .wp-block-columns, Desktop
  no-padding-mobile   padding: 0, Mobile
  no-padding-tablet   padding: 0, Tablet
  no-padding-desktop  padding: 0, Desktop
  no-margin-mobile    margin: 0, Mobile
  no-margin-tablet    margin: 0, Tablet
  no-margin-desktop   margin: 0, Desktop

--- 6. TEXT ALIGNMENT ---

Schema: .text-{align}-{breakpoint}

  Class               Effect
  ------------------- --------------------------------------
  text-left-mobile    text-align: left,   Mobile
  text-center-mobile  text-align: center, Mobile
  text-right-mobile   text-align: right,  Mobile
  (same pattern for -tablet and -desktop)

== Installation ==

1. Upload the `gutenberg-utility-classes` folder to `wp-content/plugins/`.
2. Activate the plugin via the "Plugins" menu in WordPress.
3. The stylesheet is loaded automatically on the frontend and inside the
   block editor. No configuration needed.

== Usage ==

= Example 1: Show a block only on mobile =

In the block editor, open the block's settings sidebar and add the
following to the "Additional CSS class(es)" field:

  show-on-mobile

The block will be hidden on tablet and desktop, visible only on mobile.

= Example 2: Columns block – row on mobile/tablet, stacked on desktop =

Add the following class to a Columns block:

  stack-on-desktop

(or equivalently: stack-from-desktop)

The columns will sit side by side on mobile and tablet, and stack
vertically on desktop. Useful for layouts where a sidebar should
appear below the main content on large screens.

= Example 3: Column widths per breakpoint =

Add the following classes to individual Column blocks inside a Columns
block:

  width-100-mobile  width-50-tablet  width-33-desktop

The column will take up 100 % on mobile, 50 % on tablet, and 33 % on
desktop, overriding any inline flex-basis Gutenberg may have set.

== Changelog ==

= 1.0.0 =
* Initial release.
* Visibility classes: hide-on-*, show-on-*
* Stacking classes: stack-on-*, stack-from-*
* Width utilities: width-{value}-{breakpoint} (14 values × 3 breakpoints)
* Order utilities: order-{value}-{breakpoint}
* Spacing utilities: no-gap-*, no-padding-*, no-margin-*
* Text alignment: text-{align}-{breakpoint}
