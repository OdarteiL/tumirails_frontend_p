import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { ApiService } from './api.service';
import { EstimationListResponse } from '../models/estimation.model';

@Injectable({
  providedIn: 'root'
})
export class OrganisationEstimationsService {
  constructor(private apiService: ApiService) {}

  getOrganisationEstimations(organisationId: number): Observable<EstimationListResponse> {
    return this.apiService.get<EstimationListResponse>(`/organisations/${organisationId}/estimations`);
  }
}
