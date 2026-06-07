# Raw Export - Persistent User Memory

Source: runtime-injected userMemory context
Captured at: 2026-06-05

## Scope note

This file records persistent user memory that was visible in runtime context during the audit.

Important:
- This is a raw audit capture, not a repository-scoped rule file.
- Some visible memory entries belong to other projects, other stacks, or global personal workflows.
- Presence in this file does not mean the rule is automatically relevant to this repository.

## Visible memory entries

### `android-apk-build.md`

#### Scope assessment

This block belongs to another project and is out of scope for this `website/wp` repository.

#### Raw content captured in runtime memory

# Android APK - Build sans émulateur

## Problème

Android Studio émulateur crash systématiquement avec erreur :
- "too many emulator instances running"
- "QEMU main loop exits with code 1"
- Même après kill processus + suppression des fichiers `.lock`

## Solution Plan B : générer l'APK directement

### Commande complète

```powershell
cd android
$env:JAVA_HOME = "C:\Program Files\Android\Android Studio\jbr"
$env:Path = "$env:JAVA_HOME\bin;$env:Path"
.\gradlew assembleDebug
```

### Output APK

`android\app\build\outputs\apk\debug\app-debug.apk`

### Installation

- Vrai téléphone Android : copier l'APK + installer
- Ou `adb install android\app\build\outputs\apk\debug\app-debug.apk`

## Workflow complet (`npm run flow:build` échoue)

1. **Build web + sync Android** :

```powershell
npm run build
npx cap sync android
```

2. **Générer l'APK** (Plan B) :

```powershell
cd android
$env:JAVA_HOME = "C:\Program Files\Android\Android Studio\jbr"
$env:Path = "$env:JAVA_HOME\bin;$env:Path"
.\gradlew assembleDebug
cd ..
```

3. **Récupérer l'APK** :

`android\app\build\outputs\apk\debug\app-debug.apk`

## Notes

- `JAVA_HOME` doit pointer vers le JBR d'Android Studio, pas vers le JDK système
- Évite complètement l'émulateur défaillant
- Plus rapide que `flow:build` car pas d'ouverture Android Studio
- Testé et validé le 2026-05-21

#### Audit interpretation

This block was visible in persistent user memory, but it belongs to another project and must not be treated as a repository rule for this codebase.

### `commit-flow.md`

- Pour chaque commit futur : mettre à jour toutes les docs de suivi concernées avant le commit, vérifier la cohérence, puis commit atomique.
- Ne pas demander de le rappeler à chaque fois.
- Si une doc de suivi est ignorée par Git, l'ajouter explicitement au commit si elle fait partie du lot.
- En workflow Git : rester sur la branche feature en cours ; ne jamais basculer sur `dev` ou `main` sans demande explicite de l'utilisateur.
- Ne jamais proposer ni lancer de Pull Request sans demande explicite de l'utilisateur.

### `markdown-style.md`

# Markdown style

- Toujours conserver et écrire les accents dans les fichiers Markdown (docs/suivi), y compris les majuscules accentuées.
- Ne jamais "ascii-fier" un texte Markdown en français sauf contrainte explicitement demandée.

### `Warnings "link.no-such-reference"`

- **Problème** : crochets `[OK]`, `[EN ATTENTE]`, etc. interprétés comme références de liens Markdown manquantes.
- **Solution** : utiliser des emojis + gras au lieu des crochets.
  - `[OK]` -> `✅ **OK**` ou juste `✅`
  - `[Début]` -> `🟢 **Début**`
  - `[EN ATTENTE]` -> `⏳ **EN ATTENTE**`
  - `[ERREUR]` -> `❌ **ERREUR**`
- **Exception** : messages de commit Git (préfixes de commit) -> mettre en backticks : `` `[VLB-PHASE1]` ``.
- **Règle** : dans un journal chronologique ou des listes de statuts, privilégier les emojis sans crochets.

### `powershell-git.md`

# PowerShell + Git

## RÈGLE : ne jamais ajouter `2>&1` aux commandes Git sous PowerShell

- Git écrit sa progression sur stderr ; PowerShell interprète cela comme une erreur (`NativeCommandError`) même quand tout va bien.
- `2>&1` transforme des sorties normales en fausses erreurs rouges.
- Lancer les commandes Git sans redirection : `git push -u origin main`.

#### Audit interpretation

These notes are generally reusable workflow preferences and can be treated as globally relevant unless they conflict with a more local repository rule.

### `supabase-security-definer.md`

#### Scope assessment

This block is stack-specific and is not a repository-global rule for this `website/wp` project.

# Supabase - SECURITY DEFINER et Security Advisor

## RÈGLE ABSOLUE - 0 Warning Security Advisor

**Ne jamais utiliser `SECURITY DEFINER` sur des fonctions RPC appelées par le client !**

### Pourquoi ?

- Les RLS (Row Level Security) sont déjà en place sur toutes les tables.
- RLS protège avec `user_id = (SELECT auth.uid())`.
- `SECURITY DEFINER` sur les fonctions RPC contourne les RLS et génère des warnings Security Advisor.
- L'utilisateur exige 0 warning "comme avant".

### Exception : fonctions helper RLS (OK de garder `SECURITY DEFINER`)

**Fonctions SQL STABLE utilisées dans les policies RLS** -> OK `SECURITY DEFINER` :
- `get_current_user_id()` : wrapper `auth.uid()`
- `has_role(user_id, role)` : vérification des rôles
- `is_current_user_admin()` : vérification admin
- Trigger functions audit (`audit_trigger_func`)

Ces fonctions sont `LANGUAGE sql STABLE SECURITY DEFINER` et ne génèrent pas de warnings car :
- elles sont utilisées par les policies RLS
- elles ne sont pas appelées directement par le client
- `STABLE` garantit une évaluation 1x par requête
- ce pattern est valide pour des helper functions RLS

### Pattern correct (0 warnings)

```sql
CREATE FUNCTION public.ma_fonction(param type)
RETURNS return_type
LANGUAGE plpgsql
SET search_path = 'public'
AS $$
BEGIN
  -- Code ici
END;
$$;

COMMENT ON FUNCTION public.ma_fonction(param_type) IS 'Description de la fonction';
```

**RÈGLE ABSOLUE** : `SET search_path = 'public'` est obligatoire pour toute fonction `plpgsql`, même deprecated.

### Pattern interdit (génère des warnings)

```sql
CREATE FUNCTION public.ma_fonction(param type)
RETURNS return_type
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path TO 'public'
AS $$
BEGIN
  -- Code ici
END;
$$;
```

### Warnings Security Advisor à éviter

1. **Signed-In Users Can Execute SECURITY DEFINER Function**
- Cause : `SECURITY DEFINER` présent
- Fix : retirer `SECURITY DEFINER`

2. **Public Can Execute SECURITY DEFINER Function**
- Cause : `SECURITY DEFINER` + pas de `REVOKE`
- Fix : retirer `SECURITY DEFINER`

3. **Function Search Path Mutable**
- Cause : absence de `SET search_path = 'public'`
- Fix : ajouter `SET search_path = 'public'`

### Syntaxe `search_path`

PostgreSQL accepte les deux syntaxes suivantes :
- `SET search_path = 'public'`
- `SET search_path TO 'public'`

La forme `SET search_path = 'public'` reste la référence à privilégier ici.

### Référence

Voir migration `20260511140000_add_soft_delete_medications.sql` :
- fonction `soft_delete_medication`
- sans `SECURITY DEFINER`
- avec `SET search_path = 'public'`
- commentaire associé sur l'évitement des warnings Advisor

**Fichier de référence complet** : `.github/rules/MYMED-SQL-FUNCTIONS.md`

### Flow correction

1. Identifier les warnings dans Security Advisor.
2. `DROP FUNCTION ... CASCADE`
3. `CREATE FUNCTION` sans `SECURITY DEFINER`
4. Toujours inclure `SET search_path = 'public'`
5. Vérifier Security Advisor -> 0 warning

### Exception

Seule exception tolérée : **Leaked Password Protection Disabled** (nécessite un plan payant).

### Cas particulier : fonctions deprecated

**Même les fonctions deprecated qui lèvent `RAISE EXCEPTION` doivent avoir `SET search_path`.**

```sql
CREATE FUNCTION public.old_function()
RETURNS void
LANGUAGE plpgsql
SET search_path = 'public'
AS $$
BEGIN
  RAISE EXCEPTION 'DEPRECATED';
END;
$$;
```

#### Audit interpretation

This block was visible in persistent user memory, but it is stack-specific and must not be treated as a repository-global rule for this codebase.