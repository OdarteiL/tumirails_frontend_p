import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import {
  Contact,
  ContactResponse,
  ContactListResponse,
  CreateContactRequest
} from '../models/contact.model';

@Injectable({
  providedIn: 'root'
})
export class ContactService {
  constructor(private apiService: ApiService) {}

  submitContact(data: CreateContactRequest): Observable<ContactResponse> {
    return this.apiService.post<ContactResponse>('/contact', data);
  }

  // Admin endpoints
  getContacts(): Observable<ContactListResponse> {
    return this.apiService.get<ContactListResponse>('/admin/contacts');
  }

  getContact(id: number): Observable<ContactResponse> {
    return this.apiService.get<ContactResponse>(`/admin/contacts/${id}`);
  }
}
