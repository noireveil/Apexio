# Apexio Technical Documentation & Architecture Guide

**Document Version:** 1.1.0  
**Date:** December 17, 2025  
**Author:** Muhammad Yasyfi Alhafizh

---

## Table of Contents

1. [Prologue: Philosophy & Architecture](#prologue-philosophy--architecture)
2. [Chapter I: The Entities (Models & Database)](#chapter-i-the-entities-models--database)
3. [Chapter II: The Gatekeeper (Authentication & Policies)](#chapter-ii-the-gatekeeper-authentication--policies)
4. [Chapter III: The Heart of Interaction (Livewire Components)](#chapter-iii-the-heart-of-interaction-livewire-components)
5. [Chapter IV: Aesthetics & Presentation (Bootstrap & SCSS)](#chapter-iv-aesthetics--presentation-bootstrap--scss)
6. [Epilogue: Security Notes & Best Practices](#epilogue-security-notes--best-practices)

---

## Prologue: Philosophy & Architecture

Welcome to the world of Apexio. This application is not merely a collection of code, but rather an ecosystem for project management built upon the foundation of Laravel & Livewire combined with the robustness of Bootstrap 5.

Unlike the TALL Stack trend that uses Tailwind, Apexio chooses the path of **Classic Stability**. We use Bootstrap managed through SCSS to ensure consistent design, a solid grid system, and mature UI components.

The core philosophy of this codebase is **Reactivity Without Compromise**. We avoid traditional page reloads as much as possible. Nearly all dynamic interactions—from creating projects, inviting team members, to dragging task cards—are handled by Livewire, which acts as a seamless bridge between the browser and server, while Bootstrap JS handles micro-interactions such as Modals and Dropdowns.

---

## Chapter I: The Entities (Models & Database)

Within the Apexio data universe, there are four main entities that interact with each other. They reside in `app/Models`.

### 1. The Creator: User

Everything begins with the User. This model inherits from Laravel's `Authenticatable`.

- **Identity:** Contains name, email, password, and the `avatar_path` attribute for storing profile photos.
- **Role:** Has an `is_admin` attribute to distinguish Super Admins (system rulers) from regular users.
- **Relations:** A User can own many Projects (`projects()`) and can be a member of many other projects (`belongsToMany` via pivot).

### 2. The Container: Project

Project is the gravitational center.

- **Ownership:** Each project has one absolute Owner recorded in the `owner_id` column. This is an immutable law. Only the Owner can destroy this project.
- **Lifecycle (The Cycle of Life):** In the `booted()` method, cleanup logic exists. If a Project is deleted (`deleting`), then automatically:
  - All tasks within it will be destroyed.
  - All membership relationships (`members`) will be detached. This prevents orphaned data in the database.

### 3. The Work Unit: Task

Task is the smallest atom of work.

- **Attributes:** Has status (Todo, In Progress, Done), priority (Low, Medium, High), and `due_date`.
- **Position:** Contains a `position` (or `order`) column crucial for the Drag & Drop feature in the Kanban board.

### 4. The Connector: ProjectMember (Pivot)

Although there is no explicit Model (using `belongsToMany` in User & Project), the `project_members` pivot table is where team hierarchy is determined.

- **Role:** The `role` column in this table determines whether a member is an Admin (deputy) or Member (regular citizen).

---

## Chapter II: The Gatekeeper (Authentication & Policies)

Security in Apexio is not merely an additional feature, but a fortified wall.

### Authentication (Auth Controller)

We use a modified starter kit (similar to Breeze). Authentication controllers are located in `app/Http/Controllers/Auth`.

### Authorization (Policies)

This is where the law is enforced. Located in `app/Policies`.

#### ProjectPolicy.php - The Project Constitution

This is the most sacred file in access management.

- **View:** Who can view a project? Only the Owner OR those registered in the `project_members` table.
- **Update:** Who can edit a project (change name, add members)?
  - Owner (`owner_id`): Yes.
  - Project Admin (User with 'Admin' role in pivot): Yes.
  - Regular Member: NO.
- **Delete (The Death Clause):** Who can delete a project?
  - ONLY THE OWNER (`$user->id === $project->owner_id`).
  - Project Admins DO NOT have this power. This is an absolute security feature to prevent "coups."

#### TaskPolicy.php

Governs who can move task cards around. The logic is similar to ProjectPolicy, but more flexible to allow collaboration.

---

## Chapter III: The Heart of Interaction (Livewire Components)

This is where the "magic" of this application lies. `app/Livewire` is where frontend meets backend in real-time.

### Project Management & Dashboard

**Files:** `ManageProjects.php` & `AdminDashboard.php`

The `ManageProjects` component is responsible for displaying the project list in the sidebar and main dashboard.

- **Query Logic:** Retrieves projects based on `latest()`.
- **Delete Security:** The `deleteProject($id)` function performs double-checking here. Even though the delete button is hidden in the UI, the backend still performs `$this->authorize('delete', $project)` to reject illegal requests (Inspect Element attacks).

### Membership & Roles (The Member Logic)

**Files:** `ProjectMembers.php` (Backend) & `project-members.blade.php` (Frontend)

This is the most complex component in terms of social logic.

#### Problems & Technical Solutions:

**Hydration Issue:** Initially, we stored the `$members` collection as a public property. This was fatal! When Livewire re-renders, pivot data (role) often disappeared.

- **Solution:** We retrieve member data (`$this->project->members()->withPivot('role')...`) directly in the `render()` method.

**Coup Protection:**

In the `updateRole` and `removeMember` functions, we insert a check: `if ($userId === $this->project->owner_id) return;`. The Owner cannot be demoted or kicked by anyone.

**UI Glitch (Display Jumps):**

When member status changes, the list often "flickers."

- **Solution:** We added `wire:key="member-{{ $member->id }}"` to each loop element in Blade.

**Badge Styling:**

Uses inline styles on status badges (Admin/Owner) to ensure purple and gold colors appear with high contrast, overcoming the limitations of standard Bootstrap classes.

### Kanban & Task List

**Files:** `MyTasks.php`, `TaskList.php`

Uses a sortable library that sends events to Livewire when cards are moved.

Livewire captures the event, updates the status and position in the database, then broadcasts the change so the entire team sees the update instantly.

---

## Chapter IV: Aesthetics & Presentation (Bootstrap & SCSS)

Apexio does not use utility-first CSS (Tailwind), but rather a component-based approach with compiled SCSS.

### SCSS Structure (`resources/scss`):

**app.scss:** The heart of the application's styling. This file imports the Bootstrap Framework in its entirety, giving us access to the grid system, modals, and utility classes.

**_variables.scss:** Where we redefine Bootstrap variables (such as `$primary`, `$font-family`) to match the Apexio brand identity.

### Modular Components:

- **_sidebar.scss:** Specific styling for side navigation.
- **_kanban.scss:** Manages horizontal workboard layout for smooth scrolling.
- **_modal.scss & _forms.scss:** Override default Bootstrap styles for a more modern and clean appearance.

### JavaScript Integration:

The `resources/js/bootstrap.js` file is responsible for loading the Bootstrap 5 JS library and Axios, enabling interactive features like Modal Pop-ups and Dropdown menus to function without jQuery.

---

## Epilogue: Security Notes & Best Practices

As a closing to this documentation, here are the "Security Mantras" applied in Apexio:

### Trust No One
Never trust input from the browser. Always validate on the backend (`$this->validate()`).

### Verify Authority
Don't just hide the "Delete" button. Ensure the backend function calls `$this->authorize()` before executing dangerous commands.

### Owner is King
Ensure code logic always distinguishes between `user_id` (pivot relation) and `owner_id` (actual owner in the `projects` table). Don't mix them up!

### Clean Hydration
For complex relational data (Pivot/HasMany), it's safer to retrieve it in `render()` rather than storing it in Livewire's public properties (`mount`).

---

This technical documentation has been created with care. May it serve as a guiding light for developers who continue the legacy of Apexio's codebase.