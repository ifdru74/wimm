create table if not exists m_users (
user_id INTEGER PRIMARY_KEY,
reference_id INTEGER,
user_login TEXT,
user_password TEXT,
user_name TEXT,
open_date TEXT,
close_date TEXT);

create table if not exists m_places (
place_id INTEGER PRIMARY KEY,
reference_id INTEGER,
place_name TEXT,
open_date TEXT,
close_date TEXT,
place_descr TEXT,
user_id INTEGER,
inn TEXT,
FOREIGN KEY(user_id) references m_users (user_id));

create table if not exists m_transaction_types (
t_type_id INTEGER PRIMARY KEY,
reference_id INTEGER,
t_type_name TEXT,
parent_type_id INTEGER,
type_sign INTEGER,
open_date TEXT,
is_repeat INTEGER,
period TEXT,
close_date TEXT,
user_id INTEGER,
FOREIGN KEY(user_id) references m_users (user_id));

create table if not exists m_currency (
currency_id INTEGER PRIMARY KEY,
reference_id INTEGER,
currency_name TEXT,
currency_abbr TEXT,
open_date TEXT,
close_date TEXT,
currency_sign TEXT,
user_id INTEGER,
FOREIGN KEY(user_id) references m_users (user_id));

create table if not exists m_currency_rate (
currency_rate_id INTEGER PRIMARY KEY,
reference_id INTEGER,
currency_from INTEGER,
exchange_rate_from REAL,
currency_to INTEGER,
exchange_rate_to REAL,
open_date TEXT,
close_date TEXT,
user_id INTEGER,
FOREIGN KEY(currency_from) references m_currency (currency_id),
FOREIGN KEY(currency_to) references m_currency (currency_id),
FOREIGN KEY(user_id) references m_users (user_id));

create table if not exists m_budget (
budget_id INTEGER PRIMARY KEY,
reference_id INTEGER,
budget_name TEXT,
open_date TEXT,
close_date TEXT,
budget_descr TEXT,
user_id INTEGER,
currency_id INTEGER,
FOREIGN KEY(currency_id) references m_currency (currency_id),
FOREIGN KEY(user_id) references m_users (user_id));

create table if not exists m_transactions (
transaction_id INTEGER PRIMARY KEY,
reference_id INTEGER,
transaction_name TEXT,
t_type_id INTEGER,
currency_id INTEGER,
transaction_sum REAL,
transaction_date TEXT,
user_id INTEGER,
open_date TEXT,
close_date TEXT,
place_id INTEGER,
budget_id INTEGER,
FOREIGN KEY(t_type_id) references m_transaction_types (t_type_id),
FOREIGN KEY(currency_id) references m_currency (currency_id),
FOREIGN KEY(place_id) references m_places (place_id),
FOREIGN KEY(budget_id) references m_budget (budget_id),
FOREIGN KEY(user_id) references m_users (user_id));

create table if not exists m_loans (
loan_id INTEGER PRIMARY KEY,
reference_id INTEGER,
loan_name TEXT,
loan_sum  REAL,
loan_rate REAL,
loan_type TEXT,
start_date TEXT,
end_date TEXT,
user_id  INTEGER,
place_id INTEGER,
budget_id INTEGER,
currency_id INTEGER,
open_date TEXT,
close_date TEXT,
FOREIGN KEY(currency_id) references m_currency (currency_id),
FOREIGN KEY(place_id) references m_places (place_id),
FOREIGN KEY(budget_id) references m_budget (budget_id),
FOREIGN KEY(user_id) references m_users (user_id));

select transaction_id, t_type_name, transaction_name, transaction_sum, Type_sign, transaction_date, user_name, place_name,  
                 place_descr, t.currency_id t_cid, mcu.currency_sign, mb.currency_id as bc_id  
                 from m_transactions t, m_transaction_types tt, m_users tu, m_places tp, m_currency mcu, m_budget mb  
                 where t.t_type_id=tt.t_type_id and t.user_id=tu.user_id and t.place_id=tp.place_id and t.currency_id=mcu.currency_id and  
                 t.budget_id=mb.budget_id and transaction_date>='$bd' and  transaction_date<'$ed' 

insert into m_users (user_id,user_login,user_password,user_name, open_date, close_date) values(1,'','','?', datetime(), datetime());