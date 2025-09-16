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

        interface User {
            id: number;
            name: string;
            email: string;
            email_verified_at: string;
            currency: string;
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
            dashboard: boolean;
        }
        interface Transaction {
            id: number;
            amount: number;
            value: number;
            date: string;
            description: string;
            account_id: number;
            category_id: number;
            type: 'expense' | 'income';
            is_paid: boolean;

            account: Account;
            category: Category;
            tags: Tag[];

            tag_id: number | null;
            notes?: string;
            is_fixed: boolean;
            is_recurring: boolean;
            installments?: number;
            installment_period?: 'days' | 'weeks' | 'months' | 'years';
        }

        interface Paginated<T> {
            data: T[];
            links: any[];
            meta: any;
        }
    }
}
export {};
