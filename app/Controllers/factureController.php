<?php
/**
 * FactureController - Gestion des factures
 */

class FactureController extends BaseController {

    /**
     * Liste toutes les factures
     */
    public function index() {
        $facture = new Facture();
        $page = intval($this->getQuery('page', 1));
        $search = $this->getQuery('search', '');

        if (!empty($search)) {
            $factures = $facture->rechercher($search);
            $data = [
                'factures' => $factures,
                'search' => $search,
                'user' => $this->user
            ];
        } else {
            $pagination = $facture->paginate($page, 20);
            $data = [
                'factures' => $pagination['data'],
                'pagination' => $pagination,
                'user' => $this->user
            ];
        }

        require_once ROOT_PATH . 'app/Views/factures/index.php';
    }

    /**
     * Crée une nouvelle facture (JSON)
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Méthode non autorisée', 405);
        }

        $data = $this->getJsonData();

        // Valider les données
        if (empty($data['ID_Client']) || empty($data['Objet'])) {
            $this->sendError('Client et objet requis', 400);
        }

        // Générer la référence automatique
        $facture = new Facture();
        $reference = $facture->genererReference();

        // Ajouter les données requises
        $data['Reference'] = $reference;
        $data['Date_Emission'] = $data['Date_Emission'] ?? date('Y-m-d');
        $data['Date_Echeance'] = $data['Date_Echeance'] ?? date('Y-m-d', strtotime('+30 days'));
        $data['Date_Creation'] = date('Y-m-d H:i:s');
        $data['ID_User'] = $this->user['ID_User'];
        $data['Statut'] = 'EN_ATTENTE';
        $data['Montant_Paye'] = 0;

        // Créer la facture
        $id = $facture->create($data);

        if ($id) {
            // Enregistrer les lignes
            if (isset($data['lignes']) && is_array($data['lignes'])) {
                foreach ($data['lignes'] as $ligne) {
                    $ligne['ID_Facture'] = $id;
                    $this->db->insert('LIGNES_FACTURE', $ligne);
                }
            }

            // Recalculer les totaux
            $this->updateInvoiceTotal($id);

            $this->log("Facture créée : {$reference}", 'INFO');
            $this->success('Facture créée avec succès', ['facture_id' => $id, 'reference' => $reference], 201);
        } else {
            $this->sendError('Erreur lors de la création de la facture', 500);
        }
    }

    /**
     * Affiche les détails d'une facture
     */
    public function show($id) {
        $facture = new Facture();
        $factureData = $facture->findById($id);

        if (!$factureData) {
            $this->sendError('Facture non trouvée', 404);
        }

        // Récupérer les lignes
        $lignes = $facture->getLignes($id);

        // Récupérer les informations du client
        $client = new Client();
        $clientData = $client->findById($factureData['ID_Client']);

        // Récupérer les paiements
        $query = "SELECT * FROM PAIEMENTS WHERE ID_Facture = ? ORDER BY Date_Paiement DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        $paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Paramètres entreprise
        $parametre = new ParametreEntreprise();
        $parametreData = $parametre->getParametres();

        $data = [
            'facture' => $factureData,
            'lignes' => $lignes,
            'client' => $clientData,
            'paiements' => $paiements,
            'parametres' => $parametreData,
            'user' => $this->user
        ];

        require_once ROOT_PATH . 'app/Views/factures/show.php';
    }

    /**
     * Modifie une facture (JSON)
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Méthode non autorisée', 405);
        }

        $data = $this->getJsonData();
        $facture = new Facture();

        if (!$facture->findById($id)) {
            $this->sendError('Facture non trouvée', 404);
        }

        if ($facture->update($id, $data)) {
            $this->updateInvoiceTotal($id);
            $this->log("Facture modifiée : ID {$id}", 'INFO');
            $this->success('Facture modifiée avec succès');
        } else {
            $this->sendError('Erreur lors de la modification', 500);
        }
    }

    /**
     * Annule une facture
     */
    public function cancel($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Méthode non autorisée', 405);
        }

        $facture = new Facture();
        if (!$facture->findById($id)) {
            $this->sendError('Facture non trouvée', 404);
        }

        if ($facture->update($id, ['Statut' => 'ANNULEE'])) {
            $this->log("Facture annulée : ID {$id}", 'INFO');
            $this->success('Facture annulée avec succès');
        } else {
            $this->sendError('Erreur lors de l\'annulation', 500);
        }
    }

    /**
     * Récupère les statistiques des factures (JSON)
     */
    public function stats() {
        $facture = new Facture();
        $stats = $facture->statistiques();

        $this->success('Statistiques factures', $stats);
    }

    /**
     * Récupère les factures en retard (JSON)
     */
    public function enRetard() {
        $facture = new Facture();
        $factures = $facture->enRetard();

        $this->success('Factures en retard', $factures);
    }

    /**
     * Recherche multicritère (JSON)
     */
    public function search() {
        $terme = $this->getQuery('q', '');

        if (empty($terme)) {
            $this->sendError('Terme de recherche requis', 400);
        }

        $query = "SELECT f.*, c.Nom_Client FROM FACTURES f
                  LEFT JOIN CLIENTS c ON f.ID_Client = c.ID_Client
                  WHERE f.Reference LIKE ? OR c.Nom_Client LIKE ? OR f.Objet LIKE ?
                  AND f.Statut != 'ANNULEE'
                  ORDER BY f.Date_Emission DESC";
        
        $terme = "%{$terme}%";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$terme, $terme, $terme]);
        $factures = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->success('Résultats de recherche', $factures);
    }

    /**
     * Génère le PDF d'une facture
     */
    public function generatePDF($id) {
        require_once ROOT_PATH . 'vendor/autoload.php';

        $facture = new Facture();
        $factureData = $facture->findById($id);

        if (!$factureData) {
            $this->sendError('Facture non trouvée', 404);
        }

        // Récupérer les données complètes
        $lignes = $facture->getLignes($id);
        $client = new Client();
        $clientData = $client->findById($factureData['ID_Client']);
        $parametre = new ParametreEntreprise();
        $parametreData = $parametre->getParametres();

        // Générer le HTML du PDF
        $html = $this->generateInvoiceHTML($factureData, $lignes, $clientData, $parametreData);

        // Générer avec DomPDF
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        // Envoyer le PDF
        $filename = 'FACTURE_' . $factureData['Reference'] . '.pdf';
        $dompdf->stream($filename, ['Attachment' => false]);
    }

    /**
     * Génère le HTML d'une facture
     */
    private function generateInvoiceHTML($facture, $lignes, $client, $parametres) {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                .header { display: flex; justify-content: space-between; margin-bottom: 30px; }
                .logo { font-size: 28px; font-weight: bold; color: #E8761A; }
                .ref { text-align: right; }
                .client-info { margin-bottom: 30px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th { background: #0D3B6E; color: white; padding: 8px; text-align: left; }
                td { padding: 8px; border-bottom: 1px solid #ddd; }
                .total-row { font-weight: bold; }
                .footer { margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px; font-size: 11px; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo">KT</div>
                <div class="ref">
                    <strong>' . $facture['Reference'] . '</strong><br>
                    Émise le ' . date('d/m/Y', strtotime($facture['Date_Emission'])) . '<br>
                    Échéance ' . date('d/m/Y', strtotime($facture['Date_Echeance'])) . '
                </div>
            </div>

            <div class="client-info">
                <strong>Facturé à:</strong><br>
                ' . $client['Nom_Client'] . '<br>
                ' . $client['Adresse'] . '<br>
                Tél: ' . $client['Telephone'] . '
            </div>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Désignation</th>
                        <th style="text-align: center">Qté</th>
                        <th style="text-align: right">Prix unitaire</th>
                        <th style="text-align: right">Total</th>
                    </tr>
                </thead>
                <tbody>';

        $i = 1;
        foreach ($lignes as $ligne) {
            $html .= '
                    <tr>
                        <td>' . $i . '</td>
                        <td>' . $ligne['Designation'] . '</td>
                        <td style="text-align: center">' . $ligne['Quantite'] . '</td>
                        <td style="text-align: right">' . number_format($ligne['Prix_Unitaire'], 0, ',', ' ') . ' FCFA</td>
                        <td style="text-align: right"><strong>' . number_format($ligne['Total_Ligne'], 0, ',', ' ') . ' FCFA</strong></td>
                    </tr>';
            $i++;
        }

        $html .= '
                </tbody>
            </table>

            <div style="text-align: right; margin-bottom: 20px;">
                <table style="width: 250px; margin-left: auto;">
                    <tr>
                        <td>Sous-total HT</td>
                        <td style="text-align: right"><strong>' . number_format($facture['Montant_HT'], 0, ',', ' ') . ' FCFA</strong></td>
                    </tr>
                    <tr>
                        <td>TVA (' . $facture['Taux_TVA'] . '%)</td>
                        <td style="text-align: right">' . number_format($facture['Montant_TVA'], 0, ',', ' ') . ' FCFA</td>
                    </tr>
                    <tr class="total-row">
                        <td>Total TTC</td>
                        <td style="text-align: right; font-size: 16px; color: #0D3B6E">' . number_format($facture['Montant_TTC'], 0, ',', ' ') . ' FCFA</td>
                    </tr>
                </table>
            </div>

            <div class="footer">
                <strong>Paiement:</strong> ' . number_format($facture['Montant_Paye'], 0, ',', ' ') . ' FCFA encaissé<br>
                <strong>Solde:</strong> ' . number_format($facture['Solde_Restant'], 0, ',', ' ') . ' FCFA restants<br>
                <br>
                Paiement sous 30 jours · Tout retard entraîne des pénalités de 1,5% par mois<br>
                ' . ($parametres['Email'] ?? '') . ' · ' . ($parametres['Telephone_Principal'] ?? '') . '
            </div>
        </body>
        </html>';

        return $html;
    }

    /**
     * Recalcule les totaux d'une facture
     */
    private function updateInvoiceTotal($id) {
        $query = "SELECT 
                    SUM(Total_Ligne) as montant_ht
                  FROM LIGNES_FACTURE
                  WHERE ID_Facture = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $factureStmt = $this->db->prepare("SELECT Taux_TVA FROM FACTURES WHERE ID_Facture = ?");
        $factureStmt->execute([$id]);
        $facture = $factureStmt->fetch(PDO::FETCH_ASSOC);

        $montantHT = $result['montant_ht'] ?? 0;
        $tauxTVA = $facture['Taux_TVA'] ?? 0;
        $montantTVA = $montantHT * ($tauxTVA / 100);
        $montantTTC = $montantHT + $montantTVA;

        $updateQuery = "UPDATE FACTURES 
                       SET Montant_HT = ?, Montant_TVA = ?, Montant_TTC = ?
                       WHERE ID_Facture = ?";
        
        $stmt = $this->db->prepare($updateQuery);
        $stmt->execute([$montantHT, $montantTVA, $montantTTC, $id]);
    }
}

?>
