methods: {
    handleFileUpload(event) {
        this.newPayment.file = event.target.files[0] || null;
    },

    addNewPayment() {
        if (this.newPayment.amount > this.selectedClaim.remaining_amount) {
            alert('Le montant du paiement ne peut pas dépasser le montant restant');
            return;
        }

        const formData = new FormData();
        formData.append('claim_id', this.selectedClaim.id);
        formData.append('amount', this.newPayment.amount);
        formData.append('date_of_insertion', this.newPayment.date);
        formData.append('payment_method', this.newPayment.payment_method); // corrigé
        formData.append('notes', this.newPayment.notes);
        if (this.newPayment.file) {
            formData.append('file', this.newPayment.file);
        }

        axios.post('http://127.0.0.1/tossin/api/index.php?action=newPayment', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        .then(response => {
            this.fetchClaims;
            // Réinitialiser le formulaire
            this.newPayment = {
                amount: 0,
                date: new Date().toISOString().split('T')[0],
                payment_method: '',
                notes: '',
                file: null
            };
            this.closeNewPaymentModal();
        })
        .catch(error => {
            console.error('Erreur lors de l’ajout du paiement :', error);
            alert('Une erreur est survenue lors de l’ajout du paiement.');
        });
    }
}
