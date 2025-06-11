<?php
session_start();
if (empty($_SESSION['username']) && empty($_SESSION['mdp'])) {
    header('Location: /COUD/codif/');
    exit();
}
unset($_SESSION['classe']);

include('../../traitement/fonction.php');
include('../../traitement/requete.php');
include('../../activite.php');
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestion des Stocks</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
        font-size: 1.05rem;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }

    .form-container {
        max-width: 800px;
        margin: 30px auto;
        padding: 30px;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .form-title {
        text-align: center;
        margin-bottom: 30px;
        color: #2c3e50;
        font-weight: 600;
        font-size: 1.8rem;
    }

    .form-label {
        font-weight: 500;
        margin-bottom: 10px;
        color: #2c3e50;
        font-size: 1.1rem;
    }

    .form-select,
    .select2-container .select2-selection,
    .form-control {
        width: 100% !important;
        padding: 12px 15px !important;
        border-radius: 8px !important;
        border: 1px solid #dee2e6 !important;
        transition: all 0.3s !important;
        font-size: 1rem !important;
        min-height: 48px !important;
    }

    .form-select:focus,
    .select2-container--focus .select2-selection,
    .form-control:focus {
        border-color: #3498db !important;
        box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25) !important;
    }

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
        font-size: 1rem;
        padding: 12px 15px !important;
    }

    .btn-submit {
        background-color: #3498db;
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        font-size: 1.1rem;
        transition: all 0.3s;
        width: 100%;
        margin-top: 10px;
    }

    .btn-submit:hover {
        background-color: #2980b9;
        transform: translateY(-2px);
    }

    .back-link {
        display: block;
        text-align: center;
        margin-top: 20px;
        color: #3498db;
        text-decoration: none;
        font-size: 1.05rem;
    }

    .back-link:hover {
        text-decoration: underline;
    }

    .required-field::after {
        content: " *";
        color: red;
    }

    /* Styles spécifiques pour la gestion de stock */
    .operation-type {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .operation-btn {
        flex: 1;
        margin: 0 5px;
        padding: 15px;
        text-align: center;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
        border: 2px solid #dee2e6;
        background-color: white;
        font-weight: 500;
    }

    .operation-btn.active {
        border-color: #3498db;
        background-color: #e3f2fd;
    }

    .operation-btn i {
        margin-right: 8px;
    }

    .form-section {
        margin-bottom: 25px;
        padding: 20px;
        border-radius: 8px;
        background-color: #f8fafc;
        border-left: 4px solid #3498db;
    }

    .section-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 15px;
        font-size: 1.2rem;
    }

    .item-row {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }

    .item-select {
        flex: 3;
        margin-right: 10px;
    }

    .quantity-input {
        flex: 1;
        margin-right: 10px;
    }

    .add-item-btn {
        flex: 0 0 40px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #28a745;
        color: white;
        border-radius: 8px;
        cursor: pointer;
    }

    .add-item-btn:hover {
        background-color: #218838;
    }

    .items-list {
        margin-top: 20px;
    }

    .item-card {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        background-color: white;
        border-radius: 8px;
        margin-bottom: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .item-name {
        flex: 3;
        font-weight: 500;
    }

    .item-qty {
        flex: 1;
        text-align: center;
    }

    .item-remove {
        flex: 0 0 30px;
        text-align: center;
        color: #dc3545;
        cursor: pointer;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .operation-type {
            flex-direction: column;
        }
        
        .operation-btn {
            margin: 5px 0;
        }
        
        .item-row {
            flex-direction: column;
        }
        
        .item-select, .quantity-input {
            width: 100%;
            margin-right: 0;
            margin-bottom: 10px;
        }
    }
    </style>
</head>

<body>
    <?php include('../../head.php'); ?>

    <div class="container">
        <div class="form-container">
            <h2 class="form-title"><i class="fas fa-boxes me-2"></i>Gestion des Stocks</h2>

            <form method="POST" action="./../../traitement/traitement_stock.php">
                <!-- Type d'opération -->
                <div class="operation-type">
                    <button type="button" class="operation-btn active" data-type="entree">
                        <i class="fas fa-arrow-down"></i> Entrée Stock
                    </button>
                    <button type="button" class="operation-btn" data-type="sortie">
                        <i class="fas fa-arrow-up"></i> Sortie Stock
                    </button>
                </div>
                <input type="hidden" name="operation_type" id="operation_type" value="entree">

                <!-- Section Information -->
                <div class="form-section">
                    <h4 class="section-title"><i class="fas fa-info-circle me-2"></i>Informations</h4>
                    
                    <!-- Date -->
                    <div class="mb-3">
                        <label class="form-label required-field">Date</label>
                        <input type="text" name="date_operation" class="form-control datepicker" required 
                               placeholder="Sélectionner une date" readonly>
                    </div>
                    
                    <!-- Référence -->
                    <div class="mb-3">
                        <label class="form-label required-field">Référence</label>
                        <input type="text" name="reference" class="form-control" required 
                               placeholder="N° de bon de livraison/commande...">
                    </div>
                    
                    <!-- Fournisseur/Destinataire -->
                    <div class="mb-3" id="fournisseur-field">
                        <label class="form-label required-field">Fournisseur</label>
                        <select name="fournisseur" class="form-select select2-fournisseur" required>
                            <option value="" disabled selected>Choisir un fournisseur...</option>
                            <?php
                            // Récupérer les fournisseurs depuis la base de données
                            $fournisseurs = allUtilisateurs($connexion);
                            foreach ($fournisseurs as $fournisseur) {
                                echo "<option value=\"" . htmlspecialchars($fournisseur['id']) . "\">" . 
                                     htmlspecialchars($fournisseur['nom']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <!-- Localisation -->
                    <div class="mb-3">
                        <label class="form-label required-field">Localisation</label>
                        <select name="localisation" class="form-select select2-localisation" required>
                            <option value="" disabled selected>Choisir une localisation...</option>
                            <option value="Entrepôt principal">Entrepôt principal</option>
                            <option value="Magasin">Magasin</option>
                            <option value="Atelier">Atelier</option>
                            <option value="Bureau">Bureau</option>
                        </select>
                    </div>
                    
                    <!-- Commentaire -->
                    <div class="mb-3">
                        <label class="form-label">Commentaire</label>
                        <textarea name="commentaire" class="form-control" 
                                 placeholder="Notes supplémentaires..."></textarea>
                    </div>
                </div>

                <!-- Section Articles -->
                <div class="form-section">
                    <h4 class="section-title"><i class="fas fa-box-open me-2"></i>Articles</h4>
                    
                    <!-- Ajout d'article -->
                    <div class="item-row">
                        <div class="item-select">
                            <select class="form-select select2-article" id="article_select">
                                <option value="" disabled selected>Choisir un article...</option>
                                <?php
                                // Récupérer les articles depuis la base de données
                                $articles = allUtilisateurs($connexion);
                                foreach ($articles as $article) {
                                    echo "<option value=\"" . htmlspecialchars($article['id']) . "\" 
                                          data-code=\"" . htmlspecialchars($article['profil2']) . "\">" . 
                                          htmlspecialchars($article['profil1']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="quantity-input">
                            <input type="number" id="article_qty" class="form-control" 
                                   placeholder="Qté" min="1" value="1">
                        </div>
                        <div class="add-item-btn" id="add_item_btn">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                    
                    <!-- Liste des articles ajoutés -->
                    <div class="items-list" id="items_list">
                        <!-- Les articles seront ajoutés ici dynamiquement -->
                    </div>
                </div>

                <!-- Bouton de soumission -->
                <button type="submit" class="btn btn-submit">
                    <i class="fas fa-save me-2"></i>ENREGISTRER
                </button>

                <!-- Lien de retour -->
                <a href="javascript:history.back()" class="back-link">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </form>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>

    <script>
    $(document).ready(function() {
        // Initialisation des selects
        $('.select2-fournisseur, .select2-localisation, .select2-article').select2({
            placeholder: "Sélectionner...",
            allowClear: true,
            width: '100%',
            minimumResultsForSearch: 3
        });

        // Initialisation du datepicker
        $('.datepicker').flatpickr({
            dateFormat: "d/m/Y",
            defaultDate: "today",
            locale: "fr"
        });

        // Gestion du type d'opération
        $('.operation-btn').click(function() {
            $('.operation-btn').removeClass('active');
            $(this).addClass('active');
            $('#operation_type').val($(this).data('type'));
            
            // Adaptation du formulaire selon le type d'opération
            if ($(this).data('type') === 'sortie') {
                $('#fournisseur-field label').text('Destinataire');
                $('#fournisseur-field select').attr('name', 'destinataire');
            } else if ($(this).data('type') === 'inventaire') {
                $('#fournisseur-field').hide();
            } else {
                $('#fournisseur-field').show();
                $('#fournisseur-field label').text('Fournisseur');
                $('#fournisseur-field select').attr('name', 'fournisseur');
            }
        });

        // Gestion de l'ajout d'articles
        $('#add_item_btn').click(function() {
            const articleSelect = $('#article_select');
            const articleId = articleSelect.val();
            const articleText = articleSelect.find('option:selected').text();
            const articleCode = articleSelect.find('option:selected').data('code');
            const quantity = $('#article_qty').val();
            
            if (!articleId || !quantity) {
                alert('Veuillez sélectionner un article et saisir une quantité');
                return;
            }
            
            // Vérifier si l'article est déjà dans la liste
            if ($(`#item_${articleId}`).length) {
                alert('Cet article est déjà dans la liste');
                return;
            }
            
            // Ajouter l'article à la liste
            const itemHtml = `
                <div class="item-card" id="item_${articleId}">
                    <input type="hidden" name="articles[${articleId}][id]" value="${articleId}">
                    <input type="hidden" name="articles[${articleId}][qty]" value="${quantity}">
                    <div class="item-name">${articleText} (${articleCode})</div>
                    <div class="item-qty">${quantity}</div>
                    <div class="item-remove" onclick="removeItem('${articleId}')">
                        <i class="fas fa-times"></i>
                    </div>
                </div>
            `;
            
            $('#items_list').append(itemHtml);
            
            // Réinitialiser les champs
            articleSelect.val(null).trigger('change');
            $('#article_qty').val('1');
        });

        // Permettre l'ajout avec la touche Entrée
        $('#article_qty').keypress(function(e) {
            if (e.which === 13) {
                $('#add_item_btn').click();
                return false;
            }
        });
    });

    // Fonction pour supprimer un article
    function removeItem(articleId) {
        $(`#item_${articleId}`).remove();
    }
    </script>

    <!-- Success Modal -->
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="successModalLabel">Succès</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Opération sur le stock enregistrée avec succès !
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    var modal = new bootstrap.Modal(document.getElementById('successModal'));
    modal.show();
    </script>
    <?php endif; ?>

    <?php include('../../footer.php'); ?>
</body>

</html>