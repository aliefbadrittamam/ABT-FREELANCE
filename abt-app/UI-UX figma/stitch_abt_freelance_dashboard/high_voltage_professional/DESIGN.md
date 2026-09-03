---
name: High-Voltage Professional
colors:
  surface: '#f9f9f9'
  surface-dim: '#dadada'
  surface-bright: '#f9f9f9'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f3f4'
  surface-container: '#eeeeee'
  surface-container-high: '#e8e8e8'
  surface-container-highest: '#e2e2e2'
  on-surface: '#1a1c1c'
  on-surface-variant: '#464832'
  inverse-surface: '#2f3131'
  inverse-on-surface: '#f0f1f1'
  outline: '#77795f'
  outline-variant: '#c7c9ab'
  surface-tint: '#5a6400'
  primary: '#5a6400'
  on-primary: '#ffffff'
  primary-container: '#e8ff00'
  on-primary-container: '#697400'
  inverse-primary: '#bed100'
  secondary: '#5d5e60'
  on-secondary: '#ffffff'
  secondary-container: '#dfdfe0'
  on-secondary-container: '#616364'
  tertiary: '#5f5e61'
  on-tertiary: '#ffffff'
  tertiary-container: '#f4f1f5'
  on-tertiary-container: '#6e6d71'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d9ef00'
  primary-fixed-dim: '#bed100'
  on-primary-fixed: '#1a1e00'
  on-primary-fixed-variant: '#434b00'
  secondary-fixed: '#e2e2e3'
  secondary-fixed-dim: '#c6c6c7'
  on-secondary-fixed: '#1a1c1d'
  on-secondary-fixed-variant: '#454748'
  tertiary-fixed: '#e4e1e6'
  tertiary-fixed-dim: '#c8c5ca'
  on-tertiary-fixed: '#1b1b1e'
  on-tertiary-fixed-variant: '#47464a'
  background: '#f9f9f9'
  on-background: '#1a1c1c'
  surface-variant: '#e2e2e2'
  border-subtle: '#E4E4E7'
  status-lunas: '#22C55E'
  status-dp: '#3B82F6'
  status-pending: '#F59E0B'
typography:
  headline-lg:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-bold:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.05em
  number-display:
    fontFamily: Inter
    fontSize: 40px
    fontWeight: '700'
    lineHeight: 48px
    letterSpacing: -0.03em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  margin-page: 2rem
  gutter-grid: 1.5rem
  stack-sm: 0.5rem
  stack-md: 1rem
  sidebar-width: 260px
---

## Brand & Style

The design system for this freelance management tool strikes a balance between **Modern Corporate** reliability and **High-Contrast** energy. It takes inspiration from industry-standard tools like Linear and Notion—characterized by spacious layouts, thin borders, and functional clarity—but injects a distinctive "High-Voltage" personality through its primary accent.

The target audience is a modern solo freelancer. The UI should evoke a sense of professional momentum, speed, and precision. We achieve this by using a stark, monochromatic base (White, Light Gray, and Charcoal) interrupted by a single, aggressive brand color.

**Design Principles:**
- **Functional Brutalism:** Use heavy charcoal text on neon backgrounds to ensure maximum readability and a "bold" editorial feel.
- **Data-First:** Information density is prioritized, but managed through generous whitespace and a rigid grid structure.
- **Action-Oriented:** The brand color is reserved strictly for interactive elements and current-state indicators to guide the user's eye instantly to the "next step."

## Colors

The palette is built on high-contrast relationships. The primary background is pure white to maintain a clean workspace. 

- **Primary (Neon Yellow):** Used exclusively for primary actions, active navigation states, and specific brand moments (like the dashboard income chart). Always use Charcoal text when overlaying this color.
- **Secondary (Light Gray):** Reserved for surface differentiation, such as sidebar backgrounds and secondary cards.
- **Tertiary (Charcoal):** The core color for all typography and iconography to ensure deep contrast against white and neon.
- **Status Colors:** These are semantic and separate from the brand identity. They are used in "Pill" badges for invoice and testimonial statuses. Use these at full saturation for the status indicator and a 10% opacity version for the badge background to maintain a professional look.

## Typography

This design system uses **Inter** exclusively to lean into the "System/SaaS" aesthetic. It is a highly legible typeface that feels professional and technical.

- **Headlines:** Use tight letter-spacing (`-0.02em`) for large headlines to create a compact, modern feel.
- **Data Points:** For currency values and dashboard numbers, use the `number-display` role to emphasize financial goals.
- **Labels:** Use uppercase for labels or small metadata to distinguish them from body copy.
- **Accessibility:** Given the high-vibrancy of the Neon Yellow accent, body text should never be smaller than 14px to maintain legibility against complex backgrounds.

## Layout & Spacing

The layout follows a **Fixed Sidebar / Fluid Content** model. 

- **Sidebar:** Positioned on the left with a width of `260px`. It uses the secondary background color (`#F4F4F5`) to separate it from the main workspace.
- **Grid:** Use a 12-column grid for desktop views. Content cards (like Dashboard metrics) should span columns in increments of 3, 4, or 6.
- **Reflow:** On mobile/tablet, the sidebar collapses into a bottom navigation bar or a hamburger menu, and the 12-column grid collapses into a single vertical stack with `1rem` horizontal padding.
- **Vertical Rhythm:** Maintain consistent vertical "stacks." For example, 8px (`0.5rem`) between a label and its input field, and 24px (`1.5rem`) between different sections of a form.

## Elevation & Depth

This system avoids heavy shadows, favoring **Tonal Layers** and **Low-contrast outlines** to define depth. This keeps the UI feeling flat, fast, and modern.

- **Base Layer:** The background of the application (`#FFFFFF`).
- **Surface Layer:** Cards and containers use a 1px solid border (`#E4E4E7`). 
- **Active State:** When an element is focused or active, the border color shifts to the Primary Neon Yellow or a subtle 2px stroke is added.
- **Shadows:** Only use shadows for "Floating" elements like Modals or Dropdowns. Use a "Soft Diffused" shadow: `0 10px 15px -3px rgba(0, 0, 0, 0.05)`.

## Shapes

The shape language is "Soft-Modern." We use the `rounded-xl` standard for all major components to offset the "sharpness" of the neon color and the technical typography.

- **Cards/Containers:** Use a radius of `1rem`.
- **Buttons/Inputs:** Use a radius of `0.5rem`.
- **Status Badges:** Use a fully rounded "Pill" shape (9999px) to distinguish them from interactive buttons.
- **Images/Grid Slots:** Testimonial image slots should maintain the `1rem` radius to match the container cards.

## Components

### Buttons
- **Primary:** Neon Yellow (`#E8FF00`) background with Charcoal (`#18181B`) text. No shadow, flat color.
- **Secondary:** Transparent background with a 1px border (`#E4E4E7`). Text in Charcoal.
- **Active/Hover:** For primary buttons, a slight darken or a 2px black inner border on hover.

### Form Inputs
- **Text Inputs:** White background, 1px border (`#E4E4E7`). On focus, the border changes to Charcoal or Neon Yellow.
- **Segmented Control:** Used for toggles (e.g., "Jenis Pembayaran"). The selected segment should take the Neon Yellow background.

### Cards & Lists
- **Dashboard Cards:** Simple 1px border. The "Total Pendapatan" card may feature a thicker 4px left-border in Neon Yellow for emphasis.
- **Tables:** Rows should have a subtle hover state using the secondary background color (`#F4F4F5`).

### Status Badges
- **Lunas:** Green text on 10% opacity Green background.
- **DP Terbayar:** Blue text on 10% opacity Blue background.
- **Belum Bayar:** Amber text on 10% opacity Amber background.

### Testimonial Grid
- A 2x2 grid for uploads. Each slot is a dashed-border box. Upon hover, the dash turns into a solid Neon Yellow line to indicate interactivity.