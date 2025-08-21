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
    }
}
export {};
