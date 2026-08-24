---
version: alpha
name: DSCons Community UI
description: Engineering Swiss interface for a Vietnamese engineering learning community.
colors:
  primary: "#1F77BE"
  primary-strong: "#125A96"
  primary-soft: "#E1F4F7"
  accent: "#F39402"
  ink: "#123B59"
  text: "#243746"
  muted: "#61798A"
  background: "#F5F9FC"
  surface: "#FFFFFF"
  border: "#D7E5EE"
  focus: "#F39402"
  success: "#18794E"
  danger: "#B42318"
typography:
  display: { fontFamily: Inter, fontSize: 32px, fontWeight: 750, lineHeight: 1.15, letterSpacing: -0.02em }
  heading-lg: { fontFamily: Inter, fontSize: 24px, fontWeight: 750, lineHeight: 1.25, letterSpacing: -0.015em }
  heading-md: { fontFamily: Inter, fontSize: 20px, fontWeight: 700, lineHeight: 1.3, letterSpacing: -0.01em }
  body-lg: { fontFamily: Inter, fontSize: 16px, fontWeight: 400, lineHeight: 1.65 }
  body-md: { fontFamily: Inter, fontSize: 14px, fontWeight: 400, lineHeight: 1.55 }
  body-sm: { fontFamily: Inter, fontSize: 12px, fontWeight: 400, lineHeight: 1.45 }
  label-md: { fontFamily: Inter, fontSize: 14px, fontWeight: 650, lineHeight: 1.35 }
  label-sm: { fontFamily: Inter, fontSize: 12px, fontWeight: 700, lineHeight: 1.3, letterSpacing: 0.01em }
rounded:
  none: 0px
  sm: 10px
  md: 14px
  lg: 18px
  xl: 20px
  full: 999px
spacing:
  xs: 4px
  sm: 8px
  md: 12px
  lg: 16px
  xl: 24px
  2xl: 32px
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.surface}"
    rounded: "{rounded.md}"
    height: 42px
  button-accent:
    backgroundColor: "{colors.accent}"
    textColor: "{colors.ink}"
    rounded: "{rounded.md}"
    height: 42px
  card:
    backgroundColor: "{colors.surface}"
    rounded: "{rounded.lg}"
    padding: 16px
  focus-ring:
    textColor: "{colors.focus}"
    rounded: "{rounded.sm}"
---

# DSCons Community

## Overview

DSCons is an engineering learning community for Vietnamese Cơ Điện and BIM practitioners. The interface should feel precise, trustworthy, and practical: a calm workspace for reading, learning, asking, and taking action. Content is the focus; navigation and gamification support discovery without taking over the page.

## Colors

- **DSCons blue:** `primary` is the default interactive color; `primary-strong` is used for headings, selected navigation, and high-contrast labels.
- **Technical light blue:** `primary-soft` and `background` create separation between shell, content, and cards without gradients.
- **DSCons orange:** `accent` is reserved for the most important CTA, focus ring, and small progress highlights. It must not become a second primary color.
- **Contrast:** body text uses `ink`/`text`; muted text is only used for metadata and must remain readable against `surface`.

## Typography

Inter is the product font across guest, auth, app, Livewire, and admin screens. Use the semantic scale above; avoid arbitrary font sizes unless a compact control requires it. Body copy should remain between 55–78 characters per line on desktop and use 1.5–1.7 line-height.

## Layout

The desktop app shell uses a left navigation rail, a readable central content column, and a supporting right rail. The central column owns the visual hierarchy. At widths below 1024px, collapse the right rail before shrinking the content; at mobile widths use the drawer and bottom navigation already present. Keep 16–24px page gutters and prevent horizontal overflow.

## Elevation & Depth

Use tonal surfaces and a single soft shadow (`0 8px 24px rgba(18,59,89,.08)`) for cards and dialogs. Borders remain visible so sections are understandable without shadows. Avoid blur, glass effects, and decorative gradients.

## Shapes

Cards and dialogs use 12–16px radii. Inputs and compact chips use 8px; pills are reserved for statuses and filters. Keep button geometry consistent and give important controls at least 44px height.

## Components

- **Buttons:** primary blue for the main action, orange for the single highest-priority conversion action. Every button has hover, pressed, disabled, and keyboard focus states.
- **Navigation:** active items use a blue-tinted surface and a strong blue text/icon; never rely on color alone—include the label and active background.
- **Cards:** title, metadata, content, and action are separated by spacing rather than excessive borders. Hover elevation is subtle and disabled cards remain readable.
- **Forms:** visible labels or accessible names, inline errors below fields, and loading text while Livewire actions run.
- **Dialogs/drawers:** semantic dialog roles, labelled close controls, Escape support, body scroll lock, and focus that is not hidden behind fixed navigation.
- **Loading/empty states:** reserve layout space with skeletons and provide a useful next action in empty states.

## Do's and Don'ts

- Do keep Vietnamese labels short and action-oriented.
- Do use SVG icons with accessible names; hide decorative SVGs from assistive technology.
- Do respect `prefers-reduced-motion` and avoid layout-affecting animations.
- Do test at 320, 375, 414, 768, 1024, and 1440px.
- Don't introduce new UI libraries, dark mode, gradients, or generated logos.
- Don't make a two-column card grid squeeze the primary content on small screens.
- Don't use color as the only status indicator or leave icon-only controls unlabeled.
