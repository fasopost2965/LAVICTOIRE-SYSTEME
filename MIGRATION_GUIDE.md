# E-Victoire Migration & Troubleshooting Skill

Ce guide répertorie les étapes critiques et les solutions aux problèmes rencontrés lors de la migration et de la modernisation du système.

## 📋 Protocole de Migration (Nouveaux Niveaux)

Pour chaque nouveau niveau (CE1, CE2, etc.), suivez scrupuleusement ces étapes :

### 1. Préparation des Données
- Utilisez le script `import_cp_migration.php` comme template.
- **Vérification Parent** : Assurez-vous que chaque parent a une entrée unique dans `users` et que son `childs` contient l'ID de l'élève.
- **Lien Étudiant** : Le champ `parent_id` dans la table `students` doit correspondre à l'ID du parent dans `users`.

### 2. Post-Migration (Génération d'Assets)
- Les imports directs en base de données ne génèrent pas les QR codes automatiquement.
- **Action** : Exécutez systématiquement un script de réparation (similaire à `Repair_migration.php`) pour générer les images dans `uploads/student_id_card/qrcode/` et `barcodes/`.

### 3. Configuration des Horaires (Attendance)
- Si de nouvelles classes sont créées, la table `student_attendence_schedules` doit être peuplée.
- **Horaire Standard** : 
    - Entrée : 07h30 - 09h00
    - Sortie : 15h00 - 16h30

## 🛠️ Troubleshooting (Résolution de Problèmes)

### Erreur 500 sur le Dashboard
- **Cause probable** : Session corrompue ou incomplète.
- **Solution** : 
    1. Vérifier que `Site.php` injecte bien `'role'`.
    2. **Logout / Login obligatoire** pour rafraîchir le cookie de session.

### Erreur "Undefined variable $function" ou "$role" dans les Vues
- **Cause** : Portée des variables PHP dans CodeIgniter.
- **Solution** : Dans le fichier `header.php`, récupérez les données directement depuis la session :
  ```php
  $student_session = $this->session->userdata('student');
  $role = $student_session['role'];
  ```

### QR Code "Setting not configured" lors du scan
- **Cause** : Absence de plage horaire pour la section de l'élève.
- **Solution** : Vérifier la table `student_attendence_schedules`. L'heure du serveur doit être comprise entre `entry_time_from` et `entry_time_to`.

## 🔒 Sécurité
- Supprimez toujours les scripts utilitaires (type `Repair_migration.php`) du serveur après usage.
