// resources/js/types/models.d.ts

declare global {
    namespace App.Models {
        interface Category {
            id: number;
            name: string;
            icon: string;
            color: string;
            type: 'expense' | 'income';
        }
        interface Tag {
            id: number;
            name: string;
        }
        interface BankInstitution {
            id: number;
            name: string;
            icon: string | null;
        }
        interface Account {
            id: number;
            name: string;
            initial_balance: number;
            description: string | null;
            source_of_money: string | null;
            color: string;
            bank_institution_id: number;
            bank_institution: BankInstitution;
        }
    }
}
export {};
